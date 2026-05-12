<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog\Application\Services;

use App\Catalog\Application\Services\PromCategoriesParser;
use PHPUnit\Framework\TestCase;

class PromCategoriesParserTest extends TestCase
{
    public function test_it_parses_prom_categories_tree_from_html_snippet(): void
    {
        $html   = <<<'HTML'
            <div id="spa-root">
              <div data-qaid="category-block">
                <div data-qaid="category_name">
                  <a href="/Krasota-i-zdorove">Красота и здоровье</a>
                </div>
                <div data-qaid="sub_category_block">
                  <div>
                    <div>
                      <div>
                        <a class="aV5Sw" href="/Kosmetika-po-uhodu">Косметика по уходу</a>
                      </div>
                      <div data-qaid="sub_category_name"><a href="/Uhod-za-litsom">Уход за лицом</a></div>
                      <div data-qaid="sub_category_name"><a href="/Uhod-za-volosami">Уход за волосами</a></div>
                    </div>
                    <div>
                      <div>
                        <a class="aV5Sw" href="/Tovary-dlya-zdorovya">Товары для здоровья</a>
                      </div>
                      <div data-qaid="sub_category_name"><a href="/Massazhery">Массажеры</a></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            HTML;

        $parser = new PromCategoriesParser();
        $nodes  = $parser->parse($html);

        $this->assertCount(1, $nodes);
        $this->assertSame('Красота и здоровье', $nodes[0]->name);
        $this->assertSame(0, $nodes[0]->sortOrder);
        $this->assertCount(2, $nodes[0]->children);
        $this->assertSame('Косметика по уходу', $nodes[0]->children[0]->name);
        $this->assertCount(2, $nodes[0]->children[0]->children);
        $this->assertSame('Уход за лицом', $nodes[0]->children[0]->children[0]->name);
        $this->assertSame(0, $nodes[0]->children[0]->children[0]->sortOrder);
        $this->assertSame('Товары для здоровья', $nodes[0]->children[1]->name);
        $this->assertCount(1, $nodes[0]->children[1]->children);
        $this->assertSame('Массажеры', $nodes[0]->children[1]->children[0]->name);
    }
}
