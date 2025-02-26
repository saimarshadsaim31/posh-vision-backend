<?php

namespace App\Actions;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\ShopifyAdminApi;

class UpdateProductInformation
{
    public function update(Product $product, array $input)
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

        $shopifyResponse = ShopifyAdminApi::deleteProductMedia(ProductImage::where('product_id', $product->id)->pluck('shopify_media_image_id')->toArray());
        if($shopifyResponse->status === "error") {
            return new JsonResponse([
                "message" => "An error occurred while updating product",
                "description" => $shopifyResponse->message
            ], 400);
        }

        $media = [];
        $shopifyResponse = ShopifyAdminApi::updateProduct($input, $product->shopify_product_id, count($input['images']));

        if($shopifyResponse->status === "error") {
            return new JsonResponse([
                "message" => "An error occurred while updating product",
                "description" => $shopifyResponse->message
            ], 400);
        }
        $shopify_product_link = $shopifyResponse->data['data']['productUpdate']["product"]["handle"];
        $media = $shopifyResponse->data["data"]["productUpdate"]["product"]["media"]["nodes"];

        $variant = ProductVariant::where('product_id', $product->id)->first();

        $shopifyResponse = ShopifyAdminApi::productVariantUpdate($input, $product->shopify_product_id, $variant->shopify_variant_id);
        if($shopifyResponse->status === "error") {
            return new JsonResponse([
                "message" => "An error occurred while updating product",
                "description" => $shopifyResponse->message
            ], 400);
        }

        $product->forceFill([
            'title' => $input['title'],
            'description' => $input['description'],
            'shopify_product_link' => $shopify_product_link
        ])->save();

        ProductImage::where('product_id', $product->id)->delete();

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

        $variant->update([
            'price' => $input['price']
        ]);

        return $product;
    }
}
