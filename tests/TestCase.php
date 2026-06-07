<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $database = getenv('DB_DATABASE') ?: null;

        if ((getenv('APP_ENV') ?: null) === 'testing' && $database !== 'snabix_test') {
            self::fail(sprintf(
                'Tests must use the isolated database [snabix_test], current database is [%s].',
                $database ?? 'null',
            ));
        }

        parent::setUp();
    }
}
