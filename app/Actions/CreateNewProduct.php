<?php

namespace App\Actions;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\ShopifyAdminApi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class CreateNewProduct
{
    public function create(array $input)
    {
        Validator::make($input, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required'],
            'price' => ['required', 'numeric', 'regex:/^\d{1,17}(\.\d{1,2})?$/'],
            'images' => [
                'required',
                'array'
            ]
        ])->validate();

        $collection = Collection::where('user_id', Auth::user()->id)
        ->first();

        $shopify_product_id = null;
        $shopify_product_link = null;
        $shopify_product_variant_id = null;
        $media = [];

        if($collection->shopify_collection_id !== null && $collection->shopify_collection_id !== "") {
            $shopifyResponse = ShopifyAdminApi::createProduct($input, $collection->shopify_collection_id, count($input['images']));
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while creating product",
                    "description" => $shopifyResponse->message
                ], 400);
            }
            $media = $shopifyResponse->data['data']['productCreate']['product']['media']['nodes'];
            $shopify_product_id = $shopifyResponse->data['data']['productCreate']['product']['id'];
            $shopify_product_link = $shopifyResponse->data['data']['productCreate']['product']['handle'];
            $shopify_product_variant_id = $shopifyResponse->data['data']['productCreate']['product']['variants']['nodes'][0]['id'];
            $shopifyResponse = ShopifyAdminApi::productVariantUpdate($input, $shopify_product_id, $shopify_product_variant_id);
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while creating product",
                    "description" => $shopifyResponse->message
                ], 400);
            }
            $shopifyResponse = ShopifyAdminApi::publishProductOnlineStore($shopify_product_id);
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while creating product",
                    "description" => $shopifyResponse->message
                ], 400);
            }
        }

        $product = Product::create([
            'collection_id' => $collection->id,
            'title' => $input['title'],
            'description' => $input['description'],
            'shopify_product_id' => $shopify_product_id,
            'shopify_product_link' => $shopify_product_link,
            'shopify_publication_status' => 'published'
        ]);

        function getFileIdByIndex($mediaArray, $index) {
            foreach ($mediaArray as $mediaItem) {
                if (substr($mediaItem['alt'], -1) == (string) $index) {
                    return $mediaItem['id'];
                }
            }
            return null;
        }

        $images = [];

        foreach ($input['images'] as $key => $img) {
            $images[] = [
                'image' => $img,
                'product_id' => $product->id,
                'shopify_media_image_id' => getFileIdByIndex($media, $key),
            ];
        }

        ProductImage::insert($images);

        ProductVariant::create([
            'product_id' => $product->id,
            'price' => $input['price'],
            'shopify_location_id' => env('SHOPIFY_INVENTORY_LOCATION_ID'),
            'shopify_variant_id' => $shopify_product_variant_id
        ]);

        return $product;
    }
}
