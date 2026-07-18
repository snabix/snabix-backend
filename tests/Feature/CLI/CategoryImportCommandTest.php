<?php

declare(strict_types=1);

namespace Tests\Feature\CLI;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryImportManifest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\Feature\FeatureTestCase;

class CategoryImportCommandTest extends FeatureTestCase
{
    public function test_network_import_is_blocked_without_recorded_source_rights(): void
    {
        Http::preventStrayRequests();
        $this->configurePromSource(networkEnabled: false);

        $exitCode = Artisan::call('catalog:import-categories', [
            '--source' => 'prom.ua',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame(0, EloquentCategoryImportManifest::query()->count());
        Http::assertNothingSent();
    }

    public function test_fixture_creates_preview_and_requires_explicit_approval_to_apply(): void
    {
        Http::preventStrayRequests();

        $exitCode = Artisan::call('catalog:import-categories', [
            '--source'         => 'prom.ua',
            '--source-version' => 'fixture-v1',
            '--fixture'        => base_path('tests/Fixtures/catalog/prom-categories-v1.html'),
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(0, EloquentCategory::query()->count());

        $manifest = EloquentCategoryImportManifest::query()->latest()->firstOrFail();

        $this->assertStringContainsString($manifest->id, Artisan::output());

        $exitCode = Artisan::call('catalog:import-categories', [
            '--apply' => $manifest->id,
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame(0, EloquentCategory::query()->count());

        $exitCode = Artisan::call('catalog:import-categories', [
            '--apply'   => $manifest->id,
            '--approve' => true,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(5, EloquentCategory::query()
            ->where('external_source', 'prom.ua')
            ->count());
        Http::assertNothingSent();
    }

    public function test_network_document_is_parsed_from_an_http_fake_without_contacting_the_source(): void
    {
        $contents = file_get_contents(base_path('tests/Fixtures/catalog/prom-categories-v1.html'));

        $this->assertIsString($contents);
        $this->configurePromSource(
            networkEnabled: true,
            rightsReference: 'TEST-LICENSE-REFERENCE',
        );
        Http::preventStrayRequests();
        Http::fake([
            'https://prom.ua/consumer-goods' => Http::response(
                $contents,
                200,
                ['Content-Type' => 'text/html; charset=UTF-8'],
            ),
        ]);

        $exitCode = Artisan::call('catalog:import-categories', [
            '--source' => 'prom.ua',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(0, EloquentCategory::query()->count());
        $this->assertDatabaseHas('category_import_manifests', [
            'source'         => 'prom.ua',
            'source_version' => 'prom-dom-v1',
        ]);
        Http::assertSentCount(1);
        Http::assertSent(
            fn($request): bool => $request->url() === 'https://prom.ua/consumer-goods',
        );
    }

    private function configurePromSource(
        bool $networkEnabled,
        ?string $rightsReference = null,
    ): void {
        $sources                                = config('catalog-import.sources', []);

        $this->assertIsArray($sources);
        $this->assertIsArray($sources['prom.ua'] ?? null);

        $sources['prom.ua']['network_enabled']  = $networkEnabled;
        $sources['prom.ua']['rights_reference'] = $rightsReference;

        config()->set('catalog-import.sources', $sources);
    }
}
