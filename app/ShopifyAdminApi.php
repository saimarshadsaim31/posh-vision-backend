<?php

namespace App;

use Illuminate\Http\JsonResponse;
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
                        handle
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

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 2000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Create Collection API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
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
                        handle
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

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Update Collection API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
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

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Update Collection API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
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

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }

        Log::error('Shopify Publish Collection API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
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

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "message" => $responseJson
            ];
        }
        Log::error('Shopify Unpublish Collection API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
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
                        handle
                        media(first: $media_limit) {
                            nodes {
                                id
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
                "status" => 'ACTIVE',
                "collectionsToJoin" => [$collectionId],
            ]
        ];

        if(@$request['images']) {
            if(count($request['images']) > 0) {
                $media = [];
                foreach ($request['images'] as $key => $image) {
                    array_push($media, [
                        'originalSource' => $image,
                        'mediaContentType' => 'IMAGE',
                        'alt' => $request['title'] . ' ' . $key
                    ]);
                }
                $variables['media'] = $media;
            }
        }

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Create Collection Product API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
    }
    public static function deleteProductMedia($media)
    {
        $query = <<<GRAPHQL
            mutation fileDelete(\$input: [ID!]!) {
                fileDelete(fileIds: \$input) {
                    deletedFileIds
                }
            }
        GRAPHQL;

        $variables = [
            "input" => $media
        ];

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Delete Collection Product Media API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
    }
    public static function updateProduct($request, $productId, $media_limit)
    {
        $query = <<<GRAPHQL
            mutation ProductUpdate(\$input: ProductInput!, \$media: [CreateMediaInput!]) {
                productUpdate(input: \$input, media: \$media) {
                    product {
                        id
                        title
                        descriptionHtml
                        handle
                        media(first: $media_limit) {
                            nodes {
                                id
                                alt
                                mediaContentType
                                preview {
                                    status
                                }
                            }
                        },
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            "input" => [
                "id" => $productId,
                "title" => $request['title'],
                "descriptionHtml" => $request['description']
            ]
        ];

        if(@$request['images']) {
            if(count($request['images']) > 0) {
                $media = [];
                foreach ($request['images'] as $key => $image) {
                    array_push($media, [
                        'originalSource' => $image,
                        'mediaContentType' => 'IMAGE',
                        'alt' => $request['title'] . ' ' . $key
                    ]);
                }
                $variables['media'] = $media;
            }
        }

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Update Collection Product API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
    }
    public static function deleteProduct($productId)
    {
        $query = <<<GRAPHQL
            mutation {
                productDelete(input: {id: \$productId}) {
                    deletedProductId
                }
            }
        GRAPHQL;

        $variables = [
            "productId" => $productId,
        ];

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Delete Collection Product API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
    }
    public static function publishProductOnlineStore($productId)
    {
        $query = <<<GRAPHQL
            mutation publishablePublish(\$id: ID!, \$input: [PublicationInput!]!) {
                publishablePublish(id: \$id, input: \$input) {
                    publishable {
                        availablePublicationsCount {
                            count
                        }
                        resourcePublicationsCount {
                            count
                        }
                    }
                    shop {
                        publicationCount
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            "id" => $productId,
            "input" => [
                "publicationId" => env('SHOPIFY_ONLINE_STORE_PUBLICATION_ID')
            ]
        ];

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Publish Collection Product API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
    }
    public static function unpublishProductOnlineStore($productId)
    {
        $query = <<<GRAPHQL
            mutation publishableUnpublish(\$id: ID!, \$input: [PublicationInput!]!) {
                publishableUnpublish(id: \$id, input: \$input) {
                    publishable {
                        availablePublicationsCount {
                            count
                        }
                        resourcePublicationsCount {
                            count
                        }
                    }
                    shop {
                        publicationCount
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            "id" => $productId,
            "input" => [
                "publicationId" => env('SHOPIFY_ONLINE_STORE_PUBLICATION_ID')
            ]
        ];

        $response = retry(3, function () use ($query, $variables) {
            return Http::withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Unpublish Collection Product API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
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
    public static function productVariantUpdate($request, $productId, $variantId)
    {
        $query = <<<GRAPHQL
            mutation ProductVariantsUpdate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
                productVariantsBulkUpdate(productId: \$productId, variants: \$variants) {
                    product {
                        id
                    }
                    productVariants {
                        id
                        price
                        inventoryPolicy
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $variables = [
            "productId" => $productId,
            "variants" => [
                [
                    "id" => $variantId,
                    "price" => $request['price'],
                    "inventoryPolicy" => "CONTINUE"
                ]
            ]
        ];

        $response = retry(3, function () use ($query, $variables) {
            return Http::timeout(60)->withHeaders([
                'X-Shopify-Access-Token' => env('SHOPIFY_ACCESS_TOKEN'),
                'Content-Type' => 'application/json',
            ])->post('https://'.env('SHOPIFY_STORE_DOMAIN').'/admin/api/2025-01/graphql.json', [
                'query' => $query,
                'variables' => $variables,
            ]);
        }, 1000);

        if($response->successful()) {
            $responseJson = $response->json();
            return (object) [
                "status" => "success",
                "data" => $responseJson
            ];
        }
        Log::error('Shopify Create Collection Product API error: ' . $response->body());
        return (object) [
            "status" => "error",
            "message" => $response->body()
        ];
    }
}
