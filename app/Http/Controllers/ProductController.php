<?php

namespace App\Http\Controllers;

use App\Actions\CreateNewProduct;
use App\Actions\UpdateProductInformation;
use App\Http\Resources\ProductResource;
use App\Models\Collection;
use App\Models\Product;
use App\ShopifyAdminApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $products = Product::whereHas('collection', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })
        ->where(function ($query) use ($request) {
            if(!empty($request->get('search'))) {
                $searchTerms = explode(" ", trim($request->get('search')));
                foreach ($searchTerms as $term) {
                    $query->where('title', 'ILIKE', '%'.$term.'%')
                    ->orWhere('description', 'ILIKE', '%'.$term.'%')
                    ->orWhereHas('variants', function ($q) use ($term) {
                        $q->where('price', 'LIKE', '%' . $term . '%');
                    });
                }
            }
        })
        ->paginate($perPage);
        return ProductResource::collection($products)
        ->additional([
            'meta' => [
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'last_page' => $products->lastPage(),
                'total_pages' => $products->lastPage(),
            ]
        ]);
    }
    public function store(Request $request, CreateNewProduct $creater)
    {
        $response = $creater->create($request->all());
        if(@$response->id) {
            return new JsonResponse([
                "message" => "Product successfully created",
                "product" => new ProductResource($response),
            ], 200);
        }
        return $response;
    }
    public function update(Request $request, Product $product, UpdateProductInformation $updater)
    {
        if(Product::whereHas('collection', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })
        ->where('id', $product->id)
        ->exists()) {
            $response = $updater->update($product, $request->all());
            if(@$response->id) {
                return new JsonResponse([
                    "message" => "Product successfully updated",
                    "product" => new ProductResource($product),
                ], 200);
            }
            return $response;
        }
        return new JsonResponse([], 400);
    }
    public function show(Product $product)
    {
        if(Product::whereHas('collection', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })
        ->where('id', $product->id)
        ->exists()) {
            return new JsonResponse([
                "message" => "Product successfully retrieved",
                "product" => new ProductResource($product),
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function destroy(Product $product)
    {
        if(Product::whereHas('collection', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })
        ->where('id', $product->id)
        ->exists()) {
            $shopifyResponse = ShopifyAdminApi::deleteProduct($product->shopify_product_id);
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while creating product",
                    "description" => $shopifyResponse->message
                ], 400);
            }
            $product->delete();
            return new JsonResponse([
                "message" => "Product successfully deleted",
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function collectionProduct(Request $request, Collection $collection)
    {
        if($collection->user_id === Auth::user()->id) {
            $perPage = $request->input('per_page', 20);
            $products = Product::whereHas('collection', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })
            ->where('collection_id', $collection->id)
            ->where(function ($query) use ($request) {
                if(!empty($request->get('search'))) {
                    $searchTerms = explode(" ", trim($request->get('search')));
                    foreach ($searchTerms as $term) {
                        $query->where('title', 'ILIKE', '%'.$term.'%')
                        ->orWhere('description', 'ILIKE', '%'.$term.'%')
                        ->orWhereHas('variants', function ($q) use ($term) {
                            $q->where('price', 'LIKE', '%' . $term . '%');
                        });
                    }
                }
            })
            ->paginate($perPage);
            return ProductResource::collection($products)
            ->additional([
                'meta' => [
                    'total' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'last_page' => $products->lastPage(),
                    'total_pages' => $products->lastPage(),
                ]
            ]);
        }
        return new JsonResponse([], 400);
    }
}
