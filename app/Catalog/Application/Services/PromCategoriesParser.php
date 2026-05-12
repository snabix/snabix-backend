<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\ParsedCategoryNode;
use DOMDocument;
use DOMElement;
use DOMXPath;

readonly class PromCategoriesParser
{
    /**
     * @return array<int, ParsedCategoryNode>
     */
    public function parse(string $html, string $baseUrl = 'https://prom.ua'): array
    {
        libxml_use_internal_errors(true);

        $document = new DOMDocument();
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $html);

        libxml_clear_errors();

        $xpath    = new DOMXPath($document);
        $nodes    = [];
        $categoryBlocks = $xpath->query('//div[@data-qaid="category-block"]');

        if ($categoryBlocks === false) {
            return [];
        }

        foreach ($categoryBlocks as $categoryBlock) {
            if (! $categoryBlock instanceof DOMElement) {
                continue;
            }

            $rootLink       = $this->firstElement($xpath, './/*[@data-qaid="category_name"]//a[1]', $categoryBlock);

            if ($rootLink === null) {
                continue;
            }

            $children         = [];
            $groupSortOrder   = 0;
            $subCategoryBlock = $this->firstElement($xpath, './/*[@data-qaid="sub_category_block"]', $categoryBlock);
            $groupsContainer  = $subCategoryBlock !== null
                ? ($this->firstChildElement($subCategoryBlock) ?? $subCategoryBlock)
                : null;

            foreach ($this->childElements($groupsContainer) as $subcategoryGroup) {
                $groupLink     = $this->firstElement($xpath, './/a[contains(@class, "aV5Sw")][1]', $subcategoryGroup)
                    ?? $this->firstElement($xpath, './/a[1]', $subcategoryGroup);

                if ($groupLink === null) {
                    continue;
                }

                $leafNodes     = [];
                $leafSortOrder = 0;

                $leafLinks = $xpath->query('.//*[@data-qaid="sub_category_name"]//a', $subcategoryGroup);

                if ($leafLinks === false) {
                    continue;
                }

                foreach ($leafLinks as $leafLink) {
                    if (! $leafLink instanceof DOMElement) {
                        continue;
                    }

                    $leafNode = $this->makeNodeFromLink($leafLink, $leafSortOrder);

                    if ($leafNode !== null) {
                        $leafNodes[] = $leafNode;
                        $leafSortOrder++;
                    }
                }

                $groupNode     = $this->makeNodeFromLink($groupLink, $groupSortOrder, $leafNodes);

                if ($groupNode !== null) {
                    $children[] = $groupNode;
                    $groupSortOrder++;
                }
            }

            $rootNode       = $this->makeNodeFromLink($rootLink, count($nodes), $children);

            if ($rootNode !== null) {
                $nodes[] = $rootNode;
            }
        }

        return $nodes;
    }

    /**
     * @param array<int, ParsedCategoryNode> $children
     */
    private function makeNodeFromLink(DOMElement $link, int $sortOrder, array $children = []): ?ParsedCategoryNode
    {
        $name = $this->sanitizeText($link->textContent);

        if ($name === '') {
            return null;
        }

        return new ParsedCategoryNode(
            name: $name,
            sortOrder: $sortOrder,
            children: $children,
        );
    }

    private function firstElement(DOMXPath $xpath, string $query, DOMElement $context): ?DOMElement
    {
        $result = $xpath->query($query, $context);

        if ($result === false || $result->length === 0) {
            return null;
        }

        $item   = $result->item(0);

        return $item instanceof DOMElement ? $item : null;
    }

    /**
     * @return array<int, DOMElement>
     */
    private function childElements(?DOMElement $element): array
    {
        if ($element === null) {
            return [];
        }

        $elements = [];

        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $elements[] = $childNode;
            }
        }

        return $elements;
    }

    private function firstChildElement(DOMElement $element): ?DOMElement
    {
        foreach ($element->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                return $childNode;
            }
        }

        return null;
    }

    private function sanitizeText(string $text): string
    {
        $text = str_replace("\xc2\xa0", ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }
}
