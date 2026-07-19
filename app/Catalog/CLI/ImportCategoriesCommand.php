<?php

declare(strict_types=1);

namespace App\Catalog\CLI;

use App\Catalog\Application\Services\CatalogImportSourcePolicy;
use App\Catalog\Application\Services\CategoryImporter;
use App\Catalog\Application\Services\CategorySourceFixtureLoader;
use App\Catalog\Application\Services\PromCategoriesFetcher;
use App\Catalog\Application\Services\PromCategoriesParser;
use App\Catalog\Application\Support\ParsedCategoryNode;
use App\Catalog\Infrastructure\Models\EloquentCategoryImportManifest;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Illuminate\Console\Command;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'catalog:import-categories')]
class ImportCategoriesCommand extends Command
{
    protected $signature   = 'catalog:import-categories
        {--source=prom.ua : Зарегистрированный идентификатор источника}
        {--source-version= : Версия source contract; для network берется из config}
        {--url= : URL из allowlist выбранного источника}
        {--fixture= : Утвержденная HTML fixture из разрешенной директории}
        {--apply= : Применить ранее созданный preview manifest UUID}
        {--rollback= : Откатить примененный manifest UUID}
        {--approve : Явно подтвердить apply или rollback}
        {--dry-run : Deprecated alias: сохранить preview без применения}';

    protected $description = 'Создать preview, применить или откатить воспроизводимый импорт категорий';

    public function handle(
        PromCategoriesFetcher $fetcher,
        PromCategoriesParser $parser,
        CategorySourceFixtureLoader $fixtureLoader,
        CatalogImportSourcePolicy $sourcePolicy,
        CategoryImporter $importer,
        SystemLogManager $systemLogManager,
    ): int {
        try {
            $applyManifestId    = $this->stringOption('apply');
            $rollbackManifestId = $this->stringOption('rollback');

            if ($applyManifestId !== null || $rollbackManifestId !== null) {
                return $this->handleExistingManifest(
                    importer: $importer,
                    systemLogManager: $systemLogManager,
                    applyManifestId: $applyManifestId,
                    rollbackManifestId: $rollbackManifestId,
                );
            }

            $source             = $this->stringOption('source') ?? 'prom.ua';
            $fixturePath        = $this->stringOption('fixture');
            $requestedVersion   = $this->stringOption('source-version');

            if ($fixturePath !== null) {
                $version  = $requestedVersion ?? $sourcePolicy->version($source);
                $document = $fixtureLoader->load($source, $version, $fixturePath);
            } else {
                $document = $fetcher->fetch($source, $this->stringOption('url'));

                if ($requestedVersion !== null && $requestedVersion !== $document->version) {
                    throw new RuntimeException('CLI version не совпадает с зарегистрированной network source version.');
                }
            }

            $nodes              = $parser->parse($document->contents);

            if ($nodes === []) {
                throw new RuntimeException('Парсер не смог извлечь категории из source document.');
            }

            if ($fixturePath === null && $sourcePolicy->requiresExplicitExternalIds($source)) {
                $this->assertExplicitExternalIds($nodes);
            }

            $manifest           = $importer->preview(
                nodes: $nodes,
                source: $document->source,
                sourceVersion: $document->version,
                sourceUrl: $document->sourceUrl,
            );

            $this->renderManifest($manifest);

            if ((bool) $this->option('dry-run') || ! (bool) $this->option('approve')) {
                $this->components->warn(sprintf(
                    'Preview сохранен. Для применения: php artisan catalog:import-categories --apply=%s --approve',
                    $manifest->id,
                ));

                return self::SUCCESS;
            }

            $applied            = $importer->apply($manifest->id);

            $this->components->info(sprintf('Manifest %s применен.', $applied->id));
            $this->logManifest($systemLogManager, $applied, 'apply_categories');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $systemLogManager->error(
                category: 'catalog.import',
                message: 'Импорт категорий завершился ошибкой.',
                action: 'import_categories',
                context: [
                    'source'    => $this->stringOption('source') ?? 'prom.ua',
                    'exception' => $exception->getMessage(),
                ],
            );

            $this->components->error('Импорт категорий завершился ошибкой: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }

    private function handleExistingManifest(
        CategoryImporter $importer,
        SystemLogManager $systemLogManager,
        ?string $applyManifestId,
        ?string $rollbackManifestId,
    ): int {
        if ($applyManifestId !== null && $rollbackManifestId !== null) {
            throw new InvalidArgumentException('Опции --apply и --rollback взаимоисключающие.');
        }

        if ((bool) $this->option('dry-run')) {
            throw new InvalidArgumentException('--dry-run нельзя использовать вместе с --apply или --rollback.');
        }

        if (! (bool) $this->option('approve')) {
            throw new RuntimeException('Apply и rollback требуют явный флаг --approve.');
        }

        if ($applyManifestId !== null) {
            $manifest = $importer->apply($applyManifestId);
            $this->components->info(sprintf('Manifest %s применен.', $manifest->id));
            $this->logManifest($systemLogManager, $manifest, 'apply_categories');

            return self::SUCCESS;
        }

        if ($rollbackManifestId === null) {
            throw new InvalidArgumentException('Manifest UUID не задан.');
        }

        $manifest = $importer->rollback($rollbackManifestId);
        $this->components->info(sprintf(
            'Manifest %s откатан; созданные им категории деактивированы.',
            $manifest->id,
        ));
        $this->logManifest($systemLogManager, $manifest, 'rollback_categories');

        return self::SUCCESS;
    }

    private function renderManifest(EloquentCategoryImportManifest $manifest): void
    {
        $stats = $manifest->stats;

        $this->components->info(sprintf(
            'Preview %s, checksum %s.',
            $manifest->id,
            $manifest->checksum,
        ));
        $this->table(
            ['create', 'update', 'deactivate', 'unchanged'],
            [[
                $stats['created'] ?? 0,
                $stats['updated'] ?? 0,
                $stats['deactivated'] ?? 0,
                $stats['unchanged'] ?? 0,
            ]],
        );

        $rows  = [];

        foreach ($manifest->diff as $change) {
            $before = is_array($change['before'] ?? null) ? $change['before'] : [];
            $after  = is_array($change['after'] ?? null) ? $change['after'] : [];
            $rows[] = [
                $this->displayValue($change['action'] ?? null),
                $this->displayValue($change['externalId'] ?? null),
                $this->displayValue($before['name'] ?? null),
                $this->displayValue($after['name'] ?? null),
                $this->displayValue($before['parentReference'] ?? null),
                $this->displayValue($after['parentReference'] ?? null),
            ];
        }

        if ($rows !== []) {
            $this->table(
                ['action', 'external ID', 'before', 'after', 'old parent', 'new parent'],
                $rows,
            );
        }
    }

    private function logManifest(
        SystemLogManager $systemLogManager,
        EloquentCategoryImportManifest $manifest,
        string $action,
    ): void {
        $systemLogManager->info(
            category: 'catalog.import',
            message: 'Category import manifest изменил состояние.',
            action: $action,
            context: [
                'manifest_id' => $manifest->id,
                'source'      => $manifest->source,
                'version'     => $manifest->source_version,
                'checksum'    => $manifest->checksum,
                'status'      => $manifest->status->value,
                'stats'       => $manifest->stats,
            ],
        );
    }

    private function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : null;
    }

    private function displayValue(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return '';
    }

    /**
     * @param array<int, ParsedCategoryNode> $nodes
     */
    private function assertExplicitExternalIds(array $nodes): void
    {
        foreach ($nodes as $node) {
            if (! str_starts_with($node->externalId, 'id:')) {
                throw new RuntimeException(sprintf(
                    'Network source category [%s] не содержит подтвержденный stable external ID.',
                    $node->name,
                ));
            }

            $this->assertExplicitExternalIds($node->children);
        }
    }
}
