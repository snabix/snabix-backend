<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Catalog\Application\Support\CategorySourceDocument;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

readonly class PromCategoriesFetcher
{
    public function __construct(
        private SystemLogManager $systemLogManager,
        private CatalogImportSourcePolicy $sourcePolicy,
    ) {}

    public function fetch(string $source, ?string $url = null): CategorySourceDocument
    {
        $authorizedUrl = $this->sourcePolicy->authorizeNetworkUrl($source, $url);

        try {
            $response = Http::timeout(20)
                ->connectTimeout(5)
                ->retry(2, 500)
                ->accept('text/html')
                ->withUserAgent($this->sourcePolicy->userAgent())
                ->withHeaders([
                    'Accept-Language' => 'ru',
                ])
                ->withOptions([
                    'allow_redirects' => false,
                ])
                ->get($authorizedUrl);
        } catch (Throwable $exception) {
            $this->systemLogManager->error(
                category: 'catalog.import',
                message: 'Не удалось загрузить публичную страницу категорий.',
                action: 'fetch_public_categories',
                context: [
                    'source'    => $source,
                    'url'       => $authorizedUrl,
                    'exception' => $exception->getMessage(),
                ],
            );

            throw new RuntimeException('Не удалось загрузить публичную страницу категорий.');
        }

        if (! $response->successful()) {
            $this->systemLogManager->error(
                category: 'catalog.import',
                message: 'Публичный источник категорий вернул ошибку.',
                action: 'fetch_public_categories',
                context: [
                    'source' => $source,
                    'url'    => $authorizedUrl,
                    'status' => $response->status(),
                ],
            );

            throw new RuntimeException(sprintf('Публичный источник категорий вернул ошибку. HTTP статус: %d.', $response->status()));
        }

        $contentLength = $response->header('Content-Length');
        $maxBytes      = $this->sourcePolicy->maxResponseBytes();

        if ($contentLength !== '' && is_numeric($contentLength) && (int) $contentLength > $maxBytes) {
            throw new RuntimeException('Публичный источник категорий вернул слишком большой ответ.');
        }

        $contentType   = $response->header('Content-Type');

        if ($contentType !== '' && ! str_contains(strtolower($contentType), 'text/html')) {
            throw new RuntimeException('Публичный источник категорий вернул неожиданный Content-Type.');
        }

        $html          = $response->body();

        if ($html === '' || strlen($html) > $maxBytes) {
            $this->systemLogManager->error(
                category: 'catalog.import',
                message: 'Публичный источник категорий вернул пустой ответ.',
                action: 'fetch_public_categories',
                context: [
                    'source' => $source,
                    'url'    => $authorizedUrl,
                ],
            );

            throw new RuntimeException('Публичный источник категорий вернул пустой или слишком большой ответ.');
        }

        return new CategorySourceDocument(
            source: $source,
            version: $this->sourcePolicy->version($source),
            sourceUrl: $authorizedUrl,
            contents: $html,
        );
    }
}
