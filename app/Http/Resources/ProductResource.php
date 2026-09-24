<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => (float) $this->price,
            'description' => $this->description,
            'category' => $this->category,
            'images' => $this->images,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'created_by' => $this->whenLoaded('creator', fn (): ?string => $this->creator?->name),
            'created_by_id' => $this->created_by_id,
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'updated_by' => $this->whenLoaded('updater', fn (): ?string => $this->updater?->name),
            'updated_by_id' => $this->updated_by_id,
        ];
    }
}
