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
            'collection_id' => $this->collection_id,
            'shopify_product_id' => $this->shopify_product_id,
            'shopify_product_link' => $this->shopify_product_link,
            'title' => $this->title,
            'description' => $this->description,
            'tags' => $this->tags,
            'shopify_publication_status' => $this->shopify_publication_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'images' => ProductImageResource::collection($this->images ?? []),
            'variants' => ProductVariantResource::collection($this->variants ?? [])
        ];
    }
}
