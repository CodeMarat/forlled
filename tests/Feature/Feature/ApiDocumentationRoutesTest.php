<?php

namespace Tests\Feature\Feature;

use Tests\TestCase;

class ApiDocumentationRoutesTest extends TestCase
{
    public function test_swagger_ui_page_is_accessible(): void
    {
        $response = $this->get('/api/docs');

        $response->assertOk();
        $response->assertSee('Forlled API Docs');
        $response->assertSee(route('api.openapi'), escape: false);
    }

    public function test_openapi_json_endpoint_is_accessible(): void
    {
        $response = $this->get('/api/openapi.json');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $this->assertSame(
            realpath(base_path('docs/openapi.json')),
            $response->baseResponse->getFile()->getRealPath(),
        );
    }
}
