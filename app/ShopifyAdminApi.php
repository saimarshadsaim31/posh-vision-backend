<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyAdminApi
{

    public static function createCollection ($request) {
        $query = <<<GRAPHQL
            mutation CollectionCreate(\$input: CollectionInput!) {
                collectionCreate(input: \$input) {
                    collection {
                        id
                        title
                        descriptionHtml
                        image {
                            altText
                            url
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'input' => [
                'title' => $request['title'],
                'descriptionHtml' => $request['description'],
                'image' => [
                    'altText' => $request['title'],
                    'src' => $request['image'],
                ],
            ],
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Create Collection API error: ' . $response->body());
        }
    }
    public static function updateCollection($request, $id)
    {
        $query = <<<GRAPHQL
            mutation updateCollectionRules(\$input: CollectionInput!) {
                collectionUpdate(input: \$input) {
                    collection {
                        id
                        title
                        descriptionHtml
                        image {
                            altText
                            url
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'input' => [
                'id' => $id,
                'title' => $request['title'],
                'descriptionHtml' => $request['description'],
                'image' => [
                    'altText' => $request['title'],
                    'src' => $request['image'],
                ],
            ],
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Update Collection API error: ' . $response->body());
        }
    }
    public static function deleteCollection($id)
    {
        $query = <<<GRAPHQL
            mutation collectionDelete(\$input: CollectionDeleteInput!) {
                collectionDelete(input: \$input) {
                    deletedCollectionId
                    shop {
                        id
                        name
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'input' => [
                'id' => $id,
            ]
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Update Collection API error: ' . $response->body());
        }
    }
    public static function getCollection($id)
    {
        $query = <<<GRAPHQL
            query {
                collection(id: \$id) {
                    id
                    title
                    descriptionHtml
                    handle
                    updatedAt
                    image {
                        altText
                        url
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'id' => $id
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Get Collection API error: ' . $response->body());
        }
    }
    public static function publishCollectionOnlineStore($id)
    {
        $query = <<<GRAPHQL
            mutation PublishablePublish(\$collectionId: ID!, \$publicationId: ID!) {
                publishablePublish(id: \$collectionId, input: {publicationId: \$publicationId}) {
                    publishable {
                        publishedOnPublication(publicationId: \$publicationId)
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'collectionId' => $id,
            'publicationId' => env('SHOPIFY_ONLINE_STORE_PUBLICATION_ID'),
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Publish Collection API error: ' . $response->body());
        }
    }
    public static function unpublishCollectionOnlineStore($id)
    {
        $query = <<<GRAPHQL
            mutation PublishableUnpublish(\$collectionId: ID!, \$publicationId: ID!) {
                publishableUnpublish(id: \$collectionId, input: {publicationId: \$publicationId}) {
                    publishable {
                        publishedOnPublication(publicationId: \$publicationId)
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'collectionId' => $id,
            'publicationId' => env('SHOPIFY_ONLINE_STORE_PUBLICATION_ID'),
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Unpublish Collection API error: ' . $response->body());
        }
    }
    public static function createProduct($request, $collectionId, $media_limit = 10)
    {
        $query = <<<GRAPHQL
            mutation CreateProductWithNewMedia(\$product: ProductCreateInput!, \$media: [CreateMediaInput!]) {
                productCreate(product: \$product, media: \$media) {
                    product {
                        id
                        title
                        descriptionHtml
                        status
                        media(first: $media_limit) {
                            nodes {
                                alt
                                mediaContentType
                                preview {
                                    status
                                }
                            }
                        },
                        variants(first: 1) {
                            nodes {
                                id
                                inventoryPolicy
                                inventoryQuantity
                                price
                            }
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            "product" => [
                "title" => $request['title'],
                "descriptionHtml" => $request['description'],
                "status" => env('SHOPIFY_MODE') == 'development' ? 'DRAFT' : 'ACTIVE',
                "collectionsToJoin" => [$collectionId],
            ],
            "variants" => [
                [
                    "price" => "19.99"
                ]
            ]
        ];

        if(@$request['images']) {
            if(count($request['images']) > 0) {
                $media = [];
                foreach ($request['images'] as $image) {
                    array_push($media, [
                        'originalSource' => $image,
                        'mediaContentType' => 'IMAGE',
                        'alt' => $request['title']
                    ]);
                }
                $variables['media'] = $media;
            }
        }

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            'variables' => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Create Collection Product API error: ' . $response->body());
        }
    }
    public static function updateProduct()
    {

    }
    public static function showProduct()
    {

    }
    public static function deleteProduct()
    {

    }
    public static function showProductVariantList($productId, $limit = 1)
    {
        $productId = "product_id" . $productId;
        $query = <<<GRAPHQL
            query ProductVariantsList {
                productVariants(first: $limit, query: "$productId") {
                    nodes {
                        id
                        title
                    }
                    pageInfo {
                        startCursor
                        endCursor
                    }
                }
            }
        GRAPHQL;

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Show Product Variant List API error: ' . $response->body());
        }
    }
    public static function productVariantCreate($productId, $variants)
    {
        $query = <<<GRAPHQL
            mutation ProductVariantsCreate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
                productVariantsBulkCreate(productId: \$productId, variants: \$variants) {
                    productVariants {
                        id
                        price
                        title
                        selectedOptions {
                            name
                            value
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            'productId' => $productId,
            'variants' => $variants,
        ];

        $response = Http::timeout(60)->withHeaders([
            'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
            'query' => $query,
            "variables" => $variables,
        ]);

        if($response->successful()) {
            $responseJson = $response->json();
            return $responseJson;
        } else {
            Log::error('Shopify Bulk Create Product Variants API error: ' . $response->body());
        }
    }
}
