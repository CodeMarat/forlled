<?php

namespace Tests\Unit\Unit;

use App\Http\Controllers\Api\V1\BlogPostController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Requests\Api\PaginatedIndexRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class ApiPaginationAndDocumentationTest extends TestCase
{
    public function test_paginated_index_request_has_expected_rules_and_default_per_page(): void
    {
        $request = new PaginatedIndexRequest;

        $this->assertTrue($request->authorize());
        $this->assertSame(
            [
                'page' => ['nullable', 'integer', 'min:1'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ],
            $request->rules(),
        );
        $this->assertSame(12, $request->perPage());
    }

    #[DataProvider('paginatedControllers')]
    public function test_list_controllers_accept_paginated_index_request(string $controllerClass): void
    {
        $method = new ReflectionMethod($controllerClass, 'index');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertSame(PaginatedIndexRequest::class, $parameters[0]->getType()?->getName());
    }

    public static function paginatedControllers(): array
    {
        return [
            [BlogPostController::class],
            [ProductController::class],
            [ProductCategoryController::class],
        ];
    }

    public function test_openapi_documentation_contains_pagination_parameters_for_list_endpoints(): void
    {
        $spec = json_decode((string) file_get_contents(base_path('docs/openapi.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('3.1.0', $spec['openapi']);
        $this->assertArrayHasKey('/blog-posts', $spec['paths']);
        $this->assertArrayHasKey('/products', $spec['paths']);
        $this->assertArrayHasKey('/product-categories', $spec['paths']);

        $this->assertCount(2, $spec['paths']['/blog-posts']['get']['parameters']);
        $this->assertSame('per_page', $spec['components']['parameters']['PerPage']['name']);
        $this->assertSame(12, $spec['components']['parameters']['PerPage']['schema']['default']);
        $this->assertSame(100, $spec['components']['parameters']['PerPage']['schema']['maximum']);
    }

    public function test_postman_collection_contains_expected_requests_and_variables(): void
    {
        $collection = json_decode(
            (string) file_get_contents(base_path('docs/forlled-public-api.postman_collection.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame(
            'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            $collection['info']['schema'],
        );
        $this->assertSame('Forlled Public API', $collection['info']['name']);
        $this->assertSame('base_url', $collection['variable'][0]['key']);
        $this->assertSame('http://localhost/api/v1', $collection['variable'][0]['value']);
        $this->assertSame('Pages', $collection['item'][0]['name']);
        $this->assertSame('Create Partner Request', $collection['item'][4]['item'][0]['name']);
        $this->assertSame(
            '{{base_url}}/partner-requests',
            $collection['item'][4]['item'][0]['request']['url']['raw'],
        );
    }
}
