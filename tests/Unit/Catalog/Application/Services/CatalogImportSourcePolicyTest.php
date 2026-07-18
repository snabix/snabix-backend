<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog\Application\Services;

use App\Catalog\Application\Services\CatalogImportSourcePolicy;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class CatalogImportSourcePolicyTest extends TestCase
{
    public function test_network_source_requires_feature_flag_and_rights_reference(): void
    {
        $policy = $this->app->make(CatalogImportSourcePolicy::class);

        $this->configurePromSource(networkEnabled: false);

        $this->expectException(RuntimeException::class);
        $policy->authorizeNetworkUrl('prom.ua');
    }

    public function test_network_source_accepts_only_exact_allowlisted_https_host(): void
    {
        $policy = $this->app->make(CatalogImportSourcePolicy::class);

        $this->configurePromSource(
            networkEnabled: true,
            rightsReference: 'LEGAL-2026-001',
        );

        $this->assertSame(
            'https://prom.ua/consumer-goods',
            $policy->authorizeNetworkUrl('prom.ua'),
        );

        foreach ([
            'http://prom.ua/consumer-goods',
            'https://prom.ua.evil.test/consumer-goods',
            'https://prom.ua/consumer-goods?redirect=http://127.0.0.1',
        ] as $url) {
            try {
                $policy->authorizeNetworkUrl('prom.ua', $url);
                $this->fail(sprintf('Unsafe URL [%s] was accepted.', $url));
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
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
