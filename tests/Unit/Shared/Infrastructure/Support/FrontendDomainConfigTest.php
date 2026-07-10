<?php

declare(strict_types=1);

namespace Tests\Unit\Shared\Infrastructure\Support;

use App\Shared\Infrastructure\Support\FrontendDomainConfig;
use PHPUnit\Framework\TestCase;

class FrontendDomainConfigTest extends TestCase
{
    public function test_urls_are_trimmed_deduplicated_and_keep_primary_first(): void
    {
        $this->assertSame([
            'https://app.snabix.ru',
            'https://admin.snabix.ru',
            'http://localhost:3000',
        ], FrontendDomainConfig::urls(
            'https://app.snabix.ru',
            ' https://admin.snabix.ru, https://app.snabix.ru, ',
            ['http://localhost:3000'],
        ));
    }

    public function test_stateful_domains_fall_back_and_drop_empty_values(): void
    {
        $this->assertSame([
            'localhost:3000',
            '127.0.0.1:3000',
        ], FrontendDomainConfig::statefulDomains(null, 'localhost:3000, ,127.0.0.1:3000'));
    }

    public function test_stateful_domains_use_explicit_production_values(): void
    {
        $this->assertSame([
            'app.snabix.ru',
            'admin.snabix.ru',
        ], FrontendDomainConfig::statefulDomains(
            ' app.snabix.ru,admin.snabix.ru,app.snabix.ru ',
            'localhost:3000',
        ));
    }
}
