<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Cache::remember(
            'products:index:v'.$this->productCacheVersion().':'.sha1($request->fullUrl()),
            now()->addMinutes(5),
            function () use ($request) {
                $query = Product::query()
                    ->with(['creator', 'updater'])
                    ->when($request->query('search'), function ($query, string $search): void {
                        $query->where('title', 'like', "%{$search}%");
                    })
                    ->when($request->query('category'), function ($query, string $category): void {
                        $query->where('category', $category);
                    })
                    ->latest();

                if ($request->filled('limit')) {
                    return $query->paginate(
                        perPage: max(1, min((int) $request->query('limit', 10), 100)),
                        page: max(1, (int) $request->query('page', 1))
                    );
                }

                return $query->get();
            }
        );

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();
        $product = Product::create([
            ...$request->validated(),
            'created_by_id' => $user?->id,
            'updated_by_id' => $user?->id,
        ])->load(['creator', 'updater']);

        $this->flushProductCache();

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function show(int $id): ProductResource|JsonResponse
    {
        $product = Cache::remember(
            'products:show:v'.$this->productCacheVersion().':'.$id,
            now()->addMinutes(5),
            fn (): ?Product => Product::with(['creator', 'updater'])->find($id)
        );

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, int $id): ProductResource|JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $product->update([
            ...$request->validated(),
            'updated_by_id' => $request->user()?->id,
        ]);

        $this->flushProductCache();

        return new ProductResource($product->fresh(['creator', 'updater']));
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $product->delete();

        $this->flushProductCache();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    private function productCacheVersion(): int
    {
        return (int) Cache::rememberForever('products:cache-version', fn (): int => 1);
    }

    private function flushProductCache(): void
    {
        Cache::forever('products:cache-version', $this->productCacheVersion() + 1);
    }
}
