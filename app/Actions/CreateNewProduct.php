<?php

namespace App\Actions;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateNewProduct
{
    public function create(array $input): Product
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

        $product = Product::create([
            'collection_id' => $collection->id,
            'title' => $input['title'],
            'description' => $input['description'],
        ]);

        $images = [];

        foreach ($input['images'] as $img) {
            $images[] = [
                'image' => $img,
                'product_id' => $product->id,
            ];
        }

        ProductImage::insert($images);

        ProductVariant::create([
            'product_id' => $product->id,
            'price' => $input['price']
        ]);

        return $product;
    }
}
