<?php

declare(strict_types=1);

namespace App\Catalog\Application\Services;

use App\Shared\Infrastructure\Services\SystemLogManager;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

readonly class PromCategoriesFetcher
{
    public function __construct(
        private SystemLogManager $systemLogManager,
    ) {}

    public function fetch(string $url): string
    {
        try {
            $response = Http::timeout(20)
                ->retry(2, 500)
                ->withHeaders([
                    'Accept-Language' => 'ru',
                ])
                ->get($url);
        } catch (Throwable $exception) {
            $this->systemLogManager->error(
                category: 'catalog.import',
                message: 'Не удалось загрузить публичную страницу категорий.',
                action: 'fetch_public_categories',
                context: [
                    'url'       => $url,
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
                    'url'    => $url,
                    'status' => $response->status(),
                ],
            );

            throw new RuntimeException(sprintf('Публичный источник категорий вернул ошибку. HTTP статус: %d.', $response->status()));
        }

        $html = $response->body();

        if ($html === '') {
            $this->systemLogManager->error(
                category: 'catalog.import',
                message: 'Публичный источник категорий вернул пустой ответ.',
                action: 'fetch_public_categories',
                context: [
                    'url' => $url,
                ],
            );

            throw new RuntimeException('Публичный источник категорий вернул пустой ответ.');
        }

        return $html;
    }
}
