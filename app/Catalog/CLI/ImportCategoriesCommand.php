<?php

declare(strict_types=1);

namespace App\Catalog\CLI;

use App\Catalog\Application\Services\CategoryImporter;
use App\Catalog\Application\Services\PromCategoriesFetcher;
use App\Catalog\Application\Services\PromCategoriesParser;
use App\Catalog\Application\Support\ParsedCategoryNode;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'catalog:import-categories')]
class ImportCategoriesCommand extends Command
{
    protected $signature   = 'catalog:import-categories
        {--source=prom.ua : Публичный идентификатор источника}
        {--url=https://prom.ua/consumer-goods : URL публичного каталога}
        {--dry-run : Разобрать категории без записи в базу данных}';

    protected $description = 'Импортировать публичное дерево категорий в локальный каталог';

    public function handle(
        PromCategoriesFetcher $fetcher,
        PromCategoriesParser $parser,
        CategoryImporter $importer,
        SystemLogManager $systemLogManager,
    ): int {
        $source = (string) $this->option('source');
        $url    = (string) $this->option('url');
        $dryRun = (bool) $this->option('dry-run');

        $this->components->info(sprintf('Загружаем категории из %s...', $url));

        try {
            $html        = $fetcher->fetch($url);
            $nodes       = $parser->parse($html, $url);

            if ($nodes === []) {
                $systemLogManager->error(
                    category: 'catalog.import',
                    message: 'Парсер не смог извлечь категории из публичного источника.',
                    action: 'parse_categories',
                    context: [
                        'source' => $source,
                        'url'    => $url,
                    ],
                );

                $this->components->error('Парсер не смог извлечь категории из публичного источника.');

                return self::FAILURE;
            }

            if ($dryRun) {
                $this->components->info(sprintf('Разобрано %d корневых категорий.', count($nodes)));
                $this->line(json_encode(array_map(static fn($node): array => $node->toArray(), $nodes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '[]');

                return self::SUCCESS;
            }

            $totalNodes  = $this->countNodes($nodes);

            $this->components->info(sprintf('Сохраняем %d категорий в локальный каталог...', $totalNodes));

            $progressBar = $this->output->createProgressBar($totalNodes);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage('старт');
            $progressBar->start();

            $stats       = $importer->import(
                $nodes,
                $source,
                function (ParsedCategoryNode $node, EloquentCategory $category) use ($progressBar): void {
                    $progressBar->setMessage(sprintf('%s -> %s', $node->name, $category->id));
                    $progressBar->advance();
                },
            );

            $progressBar->finish();
            $this->newLine(2);

            $systemLogManager->info(
                category: 'catalog.import',
                message: 'Импорт категорий успешно завершён.',
                action: 'import_categories',
                context: [
                    'source'  => $source,
                    'url'     => $url,
                    'created' => $stats['created'],
                    'updated' => $stats['updated'],
                    'roots'   => count($nodes),
                ],
            );

            $this->components->info(sprintf(
                'Импорт завершён. Создано: %d, обновлено: %d, корневых разделов: %d.',
                $stats['created'],
                $stats['updated'],
                count($nodes),
            ));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $systemLogManager->error(
                category: 'catalog.import',
                message: 'Импорт категорий завершился ошибкой.',
                action: 'import_categories',
                context: [
                    'source'    => $source,
                    'url'       => $url,
                    'exception' => $exception->getMessage(),
                ],
            );

            $this->components->error('Импорт категорий завершился ошибкой: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param array<int, ParsedCategoryNode> $nodes
     */
    private function countNodes(array $nodes): int
    {
        $count = 0;

        foreach ($nodes as $node) {
            $count++;
            $count += $this->countNodes($node->children);
        }

        return $count;
    }
}
