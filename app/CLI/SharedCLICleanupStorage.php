<?php

declare(strict_types=1);

namespace App\CLI;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_filter;
use function array_map;
use function fnmatch;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_string;
use function realpath;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function trim;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const FNM_PATHNAME;

#[AsCommand(name: 'shared:cleanup-storage')]
class SharedCLICleanupStorage extends Command
{
    protected $signature   = 'shared:cleanup-storage
        {--scope=* : Ограничить очистку одним или несколькими scope из config/storage-cleanup.php}
        {--days= : Переопределить retention для выбранных scope}
        {--dry-run : Показать, что будет удалено, без удаления файлов}
        {--force : Запустить даже если STORAGE_CLEANUP_ENABLED=false}';

    protected $description = 'Удалить устаревшие технические файлы из storage';

    public function handle(): int
    {
        if (! $this->isCleanupEnabled() && ! $this->option('force')) {
            $this->components->warn('Filesystem cleanup отключен через STORAGE_CLEANUP_ENABLED=false.');

            return self::SUCCESS;
        }

        $entries      = $this->resolveEntries();

        if ($entries === []) {
            $this->components->warn('Нет включенных storage cleanup scope для обработки.');

            return self::SUCCESS;
        }

        $dryRun       = (bool) $this->option('dry-run');
        $totalDeleted = 0;
        $totalBytes   = 0;

        foreach ($entries as $scope => $entry) {
            $result        = $this->cleanupEntry($scope, $entry, $dryRun);
            $totalDeleted += $result['deleted'];
            $totalBytes += $result['bytes'];

            $this->components->twoColumnDetail(
                $scope,
                sprintf('%d files, %s', $result['deleted'], $this->formatBytes($result['bytes'])),
            );
        }

        $message      = sprintf(
            '%s %d устаревших файлов (%s).',
            $dryRun ? 'Найдено' : 'Удалено',
            $totalDeleted,
            $this->formatBytes($totalBytes),
        );

        $this->components->info($message);

        return self::SUCCESS;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function resolveEntries(): array
    {
        $configuredEntries = config('storage-cleanup.entries', []);

        if (! is_array($configuredEntries)) {
            return [];
        }

        $requestedScopes   = array_filter(array_map(
            static fn(mixed $scope): string => trim((string) $scope),
            $this->option('scope'),
        ));

        $entries           = [];

        foreach ($configuredEntries as $scope => $entry) {
            if (! is_string($scope) || ! is_array($entry)) {
                continue;
            }

            $normalizedEntry = $this->normalizeEntry($entry);

            if ($requestedScopes !== [] && ! in_array($scope, $requestedScopes, true)) {
                continue;
            }

            if (! $this->isEntryEnabled($normalizedEntry)) {
                continue;
            }

            $entries[$scope] = $normalizedEntry;
        }

        return $entries;
    }

    /**
     * @param array<mixed, mixed> $entry
     *
     * @return array<string, mixed>
     */
    private function normalizeEntry(array $entry): array
    {
        $normalizedEntry = [];

        foreach ($entry as $key => $value) {
            if (is_string($key)) {
                $normalizedEntry[$key] = $value;
            }
        }

        return $normalizedEntry;
    }

    /**
     * @param array<string, mixed> $entry
     *
     * @return array{deleted: int, bytes: int}
     */
    private function cleanupEntry(string $scope, array $entry, bool $dryRun): array
    {
        $path          = $this->resolveSafePath($scope, $entry['path'] ?? null);

        if ($path === null) {
            return ['deleted' => 0, 'bytes' => 0];
        }

        $retentionDays = $this->resolveRetentionDays($entry);
        $cutoff        = CarbonImmutable::now()->subDays($retentionDays)->getTimestamp();
        $patterns      = $this->resolvePatterns($entry);
        $deleted       = 0;
        $bytes         = 0;

        foreach ($this->files($path) as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }

            if ($file->getMTime() >= $cutoff) {
                continue;
            }

            if (! $this->matchesPatterns($path, $file, $patterns)) {
                continue;
            }

            $size = $file->getSize();

            if ($dryRun || @unlink($file->getPathname())) {
                $deleted++;
                $bytes += $size;
            }
        }

        if (! $dryRun && $this->shouldDeleteEmptyDirectories($entry)) {
            $this->deleteEmptyDirectories($path);
        }

        return ['deleted' => $deleted, 'bytes' => $bytes];
    }

    private function isCleanupEnabled(): bool
    {
        $enabled = config('storage-cleanup.enabled', true);

        return is_bool($enabled) ? $enabled : (bool) $enabled;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function isEntryEnabled(array $entry): bool
    {
        $enabled = $entry['enabled'] ?? true;

        return is_bool($enabled) ? $enabled : (bool) $enabled;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function resolveRetentionDays(array $entry): int
    {
        $days           = $this->option('days');

        if (is_numeric($days) && (int) $days > 0) {
            return (int) $days;
        }

        $configuredDays = $entry['retention_days'] ?? 14;

        if (is_int($configuredDays) && $configuredDays > 0) {
            return $configuredDays;
        }

        if (is_numeric($configuredDays) && (int) $configuredDays > 0) {
            return (int) $configuredDays;
        }

        return 14;
    }

    private function resolveSafePath(string $scope, mixed $path): ?string
    {
        if (! is_string($path) || $path === '') {
            $this->components->warn(sprintf('Scope [%s] пропущен: path не задан.', $scope));

            return null;
        }

        $realPath    = realpath($path);
        $storageRoot = realpath(storage_path());

        if ($realPath === false) {
            return null;
        }

        if ($storageRoot === false || $realPath === $storageRoot) {
            $this->components->warn(sprintf('Scope [%s] пропущен: небезопасный storage path.', $scope));

            return null;
        }

        if (! str_starts_with($realPath, $storageRoot . DIRECTORY_SEPARATOR)) {
            $this->components->warn(sprintf('Scope [%s] пропущен: path вне storage.', $scope));

            return null;
        }

        return $realPath;
    }

    /**
     * @param array<string, mixed> $entry
     *
     * @return list<string>
     */
    private function resolvePatterns(array $entry): array
    {
        $patterns           = $entry['patterns'] ?? ['*'];

        if (! is_array($patterns)) {
            return ['*'];
        }

        $normalizedPatterns = [];

        foreach ($patterns as $pattern) {
            if (! is_string($pattern) || trim($pattern) === '') {
                continue;
            }

            $normalizedPatterns[] = str_replace('\\', '/', trim($pattern));
        }

        return $normalizedPatterns === [] ? ['*'] : $normalizedPatterns;
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function files(string $path): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile()) {
                yield $file;
            }
        }
    }

    /**
     * @param list<string> $patterns
     */
    private function matchesPatterns(string $root, SplFileInfo $file, array $patterns): bool
    {
        $relativePath = str_replace('\\', '/', trim(str_replace($root, '', $file->getPathname()), DIRECTORY_SEPARATOR));

        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $relativePath, FNM_PATHNAME)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function shouldDeleteEmptyDirectories(array $entry): bool
    {
        $value = $entry['delete_empty_directories'] ?? false;

        return is_bool($value) ? $value : (bool) $value;
    }

    private function deleteEmptyDirectories(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isDir()) {
                @rmdir($file->getPathname());
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return sprintf('%d B', $bytes);
        }

        $kilobytes = $bytes / 1024;

        if ($kilobytes < 1024) {
            return sprintf('%.1f KB', $kilobytes);
        }

        return sprintf('%.1f MB', $kilobytes / 1024);
    }
}
