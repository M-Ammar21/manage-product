<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_listed_with_filters_and_pagination(): void
    {
        Product::factory()->create(['title' => 'Awesome T-Shirt', 'category' => 'Clothes']);
        Product::factory()->create(['title' => 'Wireless Mouse', 'category' => 'Electronics']);

        $response = $this->getJson('/api/products?search=shirt&category=Clothes&limit=10&page=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Awesome T-Shirt')
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_product_detail_returns_not_found_when_missing(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Product not found.');
    }

    public function test_authenticated_user_can_create_product(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $token = $this->loginAndGetToken();

        $response = $this->withToken($token)->postJson('/api/products', [
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'description' => 'High-quality cotton t-shirt',
            'category' => 'Clothes',
            'images' => ['https://placehold.co/640x480'],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Awesome T-Shirt')
            ->assertJsonPath('data.created_by', 'jhon_doe');

        $this->assertDatabaseHas('products', ['title' => 'Awesome T-Shirt']);
    }

    public function test_create_product_requires_authentication(): void
    {
        $response = $this->postJson('/api/products', [
            'title' => 'Awesome T-Shirt',
            'price' => 99.99,
            'category' => 'Clothes',
            'images' => ['https://placehold.co/640x480'],
        ]);

        $response->assertUnauthorized();
    }

    public function test_create_product_validation_returns_bad_request(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $token = $this->loginAndGetToken();

        $response = $this->withToken($token)->postJson('/api/products', [
            'title' => '',
            'price' => null,
            'category' => '',
            'images' => [],
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['title', 'price', 'category', 'images']);
    }

    public function test_authenticated_user_can_update_and_delete_product(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $token = $this->loginAndGetToken();
        $product = Product::factory()->create(['title' => 'Old Product']);

        $this->withToken($token)->putJson("/api/products/{$product->id}", [
            'title' => 'Updated Product',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated Product')
            ->assertJsonPath('data.updated_by', 'jhon_doe');

        $this->withToken($token)->deleteJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    private function loginAndGetToken(): string
    {
        User::factory()->create([
            'username' => 'jhon_doe',
            'name' => 'jhon_doe',
            'password' => 'supersecret',
        ]);

        return $this->postJson('/api/auth/login', [
            'username' => 'jhon_doe',
            'password' => 'supersecret',
        ])->json('authentication_token');
    }
}
