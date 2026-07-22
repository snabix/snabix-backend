<?php

declare(strict_types=1);

namespace App\Location\CLI;

use App\Location\Application\Services\RussiaLocationImporter;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'location:import-russia')]
class ImportRussiaLocationsCommand extends Command
{
    protected $signature   = 'location:import-russia
        {--regions= : Путь к russia-regions.json}
        {--cities= : Путь к russia-cities.json}
        {--fresh : Очистить справочник регионов и городов перед импортом}
        {--dry-run : Проверить файлы и показать количество записей без записи в базу}';

    protected $description = 'Импортировать справочник регионов и городов России из JSON-файлов';

    public function handle(
        RussiaLocationImporter $importer,
        SystemLogManager       $systemLogManager,
    ): int {
        $regionsPath = $this->resolvePath($this->option('regions'), 'russia-regions.json');
        $citiesPath  = $this->resolvePath($this->option('cities'), 'russia-cities.json');
        $fresh       = (bool) $this->option('fresh');
        $dryRun      = (bool) $this->option('dry-run');

        $this->components->info('Проверяем JSON-файлы справочника локаций...');

        try {
            if ($dryRun) {
                $preview = $importer->preview($regionsPath, $citiesPath, $fresh);

                $this->components->info(sprintf(
                    'Preview готов. Регионы: +%d / обновить %d / деактивировать %d. Города: +%d / обновить %d / деактивировать %d.',
                    $this->integer($preview, 'regions_created'),
                    $this->integer($preview, 'regions_updated'),
                    $this->integer($preview, 'regions_deactivated'),
                    $this->integer($preview, 'cities_created'),
                    $this->integer($preview, 'cities_updated'),
                    $this->integer($preview, 'cities_deactivated'),
                ));
                $this->components->bulletList([
                    'Manifest: ' . $this->string($preview, 'manifest_id'),
                    'Source version: ' . $this->string($preview, 'source_version'),
                    sprintf(
                        'Записей: %d регионов, %d городов. Peak memory: %.1f MB.',
                        $this->integer($preview, 'regions'),
                        $this->integer($preview, 'cities'),
                        $this->integer($preview, 'peak_memory_bytes') / 1024 / 1024,
                    ),
                ]);

                return self::SUCCESS;
            }

            $stats = $importer->import($regionsPath, $citiesPath, $fresh);

            $systemLogManager->info(
                category: 'location.import',
                message: 'Импорт регионов и городов России успешно завершён.',
                action: 'import_russia_locations',
                context: [
                    'regions_file'       => basename($regionsPath),
                    'cities_file'        => basename($citiesPath),
                    'fresh'              => $fresh,
                    'manifest_id'        => $this->string($stats, 'manifest_id'),
                    'source_version'     => $this->string($stats, 'source_version'),
                    'regions_created'    => $this->integer($stats, 'regions_created'),
                    'regions_updated'    => $this->integer($stats, 'regions_updated'),
                    'regions_deactivated'=> $this->integer($stats, 'regions_deactivated'),
                    'cities_created'     => $this->integer($stats, 'cities_created'),
                    'cities_updated'     => $this->integer($stats, 'cities_updated'),
                    'cities_deactivated' => $this->integer($stats, 'cities_deactivated'),
                    'total_duration_ms'  => $this->integer($stats, 'total_duration_ms'),
                ],
            );

            $this->components->info(sprintf(
                'Импорт завершён. Регионы: +%d / обновлено %d / деактивировано %d. Города: +%d / обновлено %d / деактивировано %d.',
                $this->integer($stats, 'regions_created'),
                $this->integer($stats, 'regions_updated'),
                $this->integer($stats, 'regions_deactivated'),
                $this->integer($stats, 'cities_created'),
                $this->integer($stats, 'cities_updated'),
                $this->integer($stats, 'cities_deactivated'),
            ));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $systemLogManager->error(
                category: 'location.import',
                message: 'Импорт регионов и городов России завершился ошибкой.',
                action: 'import_russia_locations',
                context: [
                    'regions_file' => basename($regionsPath),
                    'cities_file'  => basename($citiesPath),
                    'fresh'        => $fresh,
                    'exception'    => $exception->getMessage(),
                ],
            );

            $this->components->error('Импорт завершился ошибкой: ' . $exception->getMessage());

            return self::FAILURE;
        }
    }

    private function resolvePath(mixed $value, string $fileName): string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return base_path($fileName);
    }

    /**
     * @param array<string, int|string> $values
     */
    private function integer(array $values, string $key): int
    {
        $value = $values[$key] ?? 0;

        return is_int($value) ? $value : 0;
    }

    /**
     * @param array<string, int|string> $values
     */
    private function string(array $values, string $key): string
    {
        $value = $values[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
