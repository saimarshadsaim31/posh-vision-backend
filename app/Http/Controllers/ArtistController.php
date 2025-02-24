<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Collection;
use App\Models\Product;
use App\Models\User;
use App\ShopifyAdminApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ArtistController extends Controller
{
    private function collectionStatus(Collection $collection, $request)
    {
        $data = [
            "title" => $collection->title,
            "description" => $collection->description,
            "image" => $collection->image
        ];

        if(($collection->shopify_collection_id === null || $collection->shopify_collection_id === "") && $request->input('status') === 'approved') {
            $shopifyResponse = ShopifyAdminApi::createCollection($data);
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while changing collection status",
                    "description" => $shopifyResponse->message
                ], 400);
            }
            $collection->update([
                "shopify_collection_id" => $shopifyResponse->data["data"]["collectionCreate"]["collection"]["id"],
                "shopify_collection_link" => $shopifyResponse->data["data"]["collectionCreate"]["collection"]["handle"]
            ]);
            $shopifyResponse = ShopifyAdminApi::publishCollectionOnlineStore($collection->shopify_collection_id);
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while changing collection status",
                    "description" => $shopifyResponse->message
                ], 400);
            }
            $collection->update([
                "shopify_publication_status" => "published"
            ]);
        } else if ($request->input('status') === 'approved') {
            $shopifyResponse = ShopifyAdminApi::publishCollectionOnlineStore($collection->shopify_collection_id);
            if($shopifyResponse->status === "error") {
                return new JsonResponse([
                    "message" => "An error occurred while changing collection status",
                    "description" => $shopifyResponse->message
                ], 400);
            }
            $collection->update([
                "shopify_publication_status" => "published"
            ]);
        } else if ($request->input('status') === 'rejected') {
            if ($collection->shopify_collection_id) {
                $shopifyResponse = ShopifyAdminApi::unpublishCollectionOnlineStore($collection->shopify_collection_id);
                if($shopifyResponse->status === "error") {
                    return new JsonResponse([
                        "message" => "An error occurred while changing collection status",
                        "description" => $shopifyResponse->message
                    ], 400);
                }
                $collection->update([
                    "shopify_publication_status" => "draft"
                ]);
            }
        }
        $collection->update([
            "status" => $request->input('status'),
        ]);
    }
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $artists = User::where('role', 'artist')
        ->where(function ($query) use ($request) {
            if(!empty($request->get('status'))) {
                $query->whereHas('collections', function ($q) use ($request) {
                    $q->where('status', $request->get('status'));
                });
            }
            if(!empty($request->get('search'))) {
                $searchTerms = explode(" ", trim($request->get('search')));
                foreach ($searchTerms as $term) {
                    $query->where('first_name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('last_name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('email', 'ILIKE', '%'.$term.'%')
                    ->orWhere('phone_number', 'ILIKE', '%'.$term.'%')
                    ->orWhere('country', 'ILIKE', '%'.$term.'%')
                    ->orWhere('city', 'ILIKE', '%'.$term.'%')
                    ->orWhere('address', 'ILIKE', '%'.$term.'%')
                    ->orWhere('zip_code', 'ILIKE', '%'.$term.'%')
                    ->orWhere('state', 'ILIKE', '%'.$term.'%')
                    ->orWhereHas('collections', function ($q) use ($term) {
                        $q->where('title', 'ILIKE', '%'.$term.'%')
                        ->orWhere('description', 'ILIKE', '%'.$term.'%');
                    });
                }
            }
        })
        ->paginate($perPage);
        return UserResource::collection($artists)
            ->additional([
                'meta' => [
                    'total' => $artists->total(),
                    'current_page' => $artists->currentPage(),
                    'per_page' => $artists->perPage(),
                    'last_page' => $artists->lastPage(),
                    'total_pages' => $artists->lastPage(),
                ]
            ]);
    }
    public function store(Request $request, CreateNewUser $creater)
    {
        $user = $creater->create($request->all());
        return new JsonResponse([
            "message" => "Artist successfully created",
            "user" => new UserResource($user),
        ], 200);
    }
    public function update(Request $request, User $artist, UpdateUserProfileInformation $updater)
    {
        if($artist->role === 'artist') {
            $updater->update($artist, $request->all());
            if(in_array($request->input('status'), ['rejected', 'approved'])) {
                $collection = Collection::where('user_id', $artist->id)->first();
                $this->collectionStatus($collection, $request);
            }
            return new JsonResponse([
                "message" => "Artist successfully updated",
                "user" => new UserResource($artist),
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function destroy(User $artist)
    {
        if($artist->role === 'artist') {
            $collection = Collection::where('user_id', $artist->id)->first();
            if($collection->shopify_collection_id !== null && $collection->shopify_collection_id !== "") {

            }
            $artist->delete();
            return new JsonResponse([
                "message" => "Artist successfully deleted"
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function show(User $artist)
    {
        if($artist->role === 'artist') {
            return new JsonResponse([
                "message" => "Artist successfully retrieved",
                "user" => new UserResource($artist),
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function updatePassword(Request $request, User $artist, UpdateUserPassword $updater)
    {
        if($artist->role === 'artist') {
            $updater->update($artist, $request->all());
            return new JsonResponse([
                "message" => "Artist password successfully updated",
            ], 400);
        }
        return new JsonResponse([], 400);
    }
    public function handleAccess(User $artist)
    {
        if($artist->role === 'artist') {
            $artist->update([
                'blocked' => !$artist->blocked
            ]);
            return new JsonResponse([
                "message" => "Artist access successfully updated"
            ], 200);
        }
        return new JsonResponse([], 400);
    }
    public function handleStatus(User $artist, Request $request)
    {
        Validator::make($request->all(), [
            'status' => 'required|in:rejected,approved',
        ])->validate();
        if($artist->role === 'artist') {
            $collection = Collection::where('user_id', $artist->id)->first();
            if($collection) {
                $this->collectionStatus($collection, $request);
                return new JsonResponse([
                    "message" => "Artist profile status successfully updated",
                ], 200);
            }
        }
        return new JsonResponse([], 400);
    }
    public function collectionProduct(User $artist, Request $request)
    {
        if($artist->role === 'artist') {
            $perPage = $request->input('per_page', 20);
            $products = Product::whereHas('collection', function ($query) use ($artist) {
                $query->where('user_id', $artist->id);
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
        return new JsonResponse([], 400);
    }
}
