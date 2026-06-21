<?php

declare(strict_types=1);

namespace Tests\Feature\Docs;

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScrambleDocumentationTest extends TestCase
{
    public function test_scramble_analysis_completes_successfully(): void
    {
        $exitCode = Artisan::call('scramble:analyze');

        $this->assertSame(0, $exitCode);
    }

    public function test_scramble_http_endpoints_are_accessible(): void
    {
        $this->withoutMiddleware(RestrictedDocsAccess::class);

        $this->get('/docs/api.json')
            ->assertOk()
            ->assertHeader('content-type', 'application/json');

        $this->get('/docs/api')
            ->assertOk()
            ->assertSee('api', escape: false);
    }

    public function test_shared_output_resources_keep_explicit_response_schemas(): void
    {
        $this->withoutMiddleware(RestrictedDocsAccess::class);

        $schemas = $this->get('/docs/api.json')
            ->assertOk()
            ->json('components.schemas');

        $this->assertIsArray($schemas);
        $this->assertSame('object', data_get($schemas, 'ListListingsResponse.type'));
        $this->assertSame(['items', 'meta'], data_get($schemas, 'ListListingsResponse.required'));
        $this->assertSame('array', data_get($schemas, 'ListListingsResponse.properties.items.type'));
        $this->assertSame('object', data_get($schemas, 'ListListingsResponse.properties.meta.type'));
        $this->assertSame(['string', 'null'], data_get($schemas, 'ProfileResponse.properties.phoneNumber.type'));
        $this->assertSame('object', data_get($schemas, 'CreateListingResponse.type'));
    }
}
