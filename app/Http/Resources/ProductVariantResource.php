<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            'price' => $this->price,
            'shopify_variant_id' => $this->shopify_variant_id,
            'shopify_inventory_item_id' => $this->shopify_inventory_item_id,
            'shopify_location_id' => $this->shopify_location_id,
            'inventory_available' => $this->inventory_available
        ];
    }
}
