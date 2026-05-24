<?php

declare(strict_types=1);

namespace App\CLI;

use App\Location\Application\Services\RussiaLocationImporter;
use App\Shared\Infrastructure\Services\SystemLogManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Throwable;

#[AsCommand(name: 'location:import-russia')]
class LocationCLIImportRussiaLocations extends Command
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
                $preview = $importer->preview($regionsPath, $citiesPath);

                $this->components->info(sprintf(
                    'Файлы корректны. Регионов: %d, городов: %d.',
                    $preview['regions'],
                    $preview['cities'],
                ));

                return self::SUCCESS;
            }

            $stats = $importer->import($regionsPath, $citiesPath, $fresh);

            $systemLogManager->info(
                category: 'location.import',
                message: 'Импорт регионов и городов России успешно завершён.',
                action: 'import_russia_locations',
                context: [
                    'regions_path'    => $regionsPath,
                    'cities_path'     => $citiesPath,
                    'fresh'           => $fresh,
                    'regions_created' => $stats['regions_created'],
                    'regions_updated' => $stats['regions_updated'],
                    'cities_created'  => $stats['cities_created'],
                    'cities_updated'  => $stats['cities_updated'],
                ],
            );

            $this->components->info(sprintf(
                'Импорт завершён. Регионы: +%d / обновлено %d. Города: +%d / обновлено %d.',
                $stats['regions_created'],
                $stats['regions_updated'],
                $stats['cities_created'],
                $stats['cities_updated'],
            ));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $systemLogManager->error(
                category: 'location.import',
                message: 'Импорт регионов и городов России завершился ошибкой.',
                action: 'import_russia_locations',
                context: [
                    'regions_path' => $regionsPath,
                    'cities_path'  => $citiesPath,
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
}
