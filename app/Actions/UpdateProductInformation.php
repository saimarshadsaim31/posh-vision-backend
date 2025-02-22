<?php

namespace App\Actions;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        $product->forceFill([
            'title' => $input['title'],
            'description' => $input['description'],
        ])->save();

        ProductImage::where('product_id', $product->id)->delete();

        $images = [];

        foreach ($input['images'] as $img) {
            $images[] = [
                'image' => $img,
                'product_id' => $product->id,
            ];
        }

        ProductImage::insert($images);

        ProductVariant::where('product_id', $product->id)->delete();

        ProductVariant::create([
            'product_id' => $product->id,
            'price' => $input['price']
        ]);
    }
}
