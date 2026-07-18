<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog\Application\Services;

use App\Catalog\Application\Services\PromCategoriesParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PromCategoriesParserTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function fixtureProvider(): iterable
    {
        yield 'initial contract' => [
            'prom-categories-v1.html',
            'Root Alpha',
            'Leaf One',
        ];
        yield 'rename and move contract' => [
            'prom-categories-v2.html',
            'Root Alpha Renamed',
            'Leaf One Renamed',
        ];
    }

    #[DataProvider('fixtureProvider')]
    public function test_it_parses_stable_external_ids_from_local_contract_fixture(
        string $fixture,
        string $rootName,
        string $leafName,
    ): void {
        $html  = file_get_contents(dirname(__DIR__, 4) . '/Fixtures/catalog/' . $fixture);

        $this->assertIsString($html);

        $nodes = (new PromCategoriesParser())->parse($html);

        $this->assertCount(2, $nodes);
        $this->assertSame('id:root-a', $nodes[0]->externalId);
        $this->assertSame($rootName, $nodes[0]->name);
        $this->assertSame('id:root-b', $nodes[1]->externalId);

        $group = $fixture === 'prom-categories-v1.html'
            ? $nodes[0]->children[0]
            : $nodes[1]->children[0];

        $this->assertSame('id:group-one', $group->externalId);
        $this->assertSame('id:leaf-one', $group->children[0]->externalId);
        $this->assertSame($leafName, $group->children[0]->name);
    }
}
