<?php

declare(strict_types=1);

namespace App\Media\CLI;

use App\Media\Domain\Enums\MediaType;
use App\Media\Infrastructure\Models\EloquentMedia;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;

use function array_filter;
use function array_map;
use function array_unique;
use function is_numeric;
use function is_string;
use function realpath;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function trim;
use function unlink;

use const DIRECTORY_SEPARATOR;

#[AsCommand(name: 'media:cleanup-orphans')]
class CleanupOrphanFilesCommand extends Command
{
    protected $signature   = 'media:cleanup-orphans
        {--disk=* : Ограничить проверку конкретным disk из filesystems.php}
        {--days=7 : Удалять только orphan-файлы старше указанного количества дней}
        {--force : Реально удалить файлы. Без этого флага команда работает как dry-run}';

    protected $description = 'Найти и удалить постоянные media-файлы без записей в таблице media';

    public function handle(): int
    {
        $force           = (bool) $this->option('force');
        $retentionDays   = $this->resolveRetentionDays();
        $cutoffTimestamp = CarbonImmutable::now()->subDays($retentionDays)->getTimestamp();
        $disks           = $this->resolveDisks();

        if ($disks === []) {
            $this->components->warn('Нет local filesystem disks для проверки orphan media.');

            return self::SUCCESS;
        }

        $totalFiles      = 0;
        $totalBytes      = 0;

        foreach ($disks as $disk) {
            $root             = $this->resolveDiskRoot($disk);

            if ($root === null) {
                continue;
            }

            $knownDirectories = $this->knownMediaDirectories($disk);
            $result           = $this->cleanupDisk($root, $knownDirectories, $cutoffTimestamp, $force);
            $totalFiles += $result['files'];
            $totalBytes += $result['bytes'];

            $this->components->twoColumnDetail(
                $disk,
                sprintf('%d files, %s', $result['files'], $this->formatBytes($result['bytes'])),
            );
        }

        $this->components->info(sprintf(
            '%s %d orphan media files (%s).',
            $force ? 'Удалено' : 'Найдено',
            $totalFiles,
            $this->formatBytes($totalBytes),
        ));

        if (! $force) {
            $this->components->warn('Dry-run режим: добавьте --force для удаления найденных orphan-файлов.');
        }

        return self::SUCCESS;
    }

    private function resolveRetentionDays(): int
    {
        $days = $this->option('days');

        if (is_numeric($days) && (int) $days >= 0) {
            return (int) $days;
        }

        return 7;
    }

    /**
     * @return list<string>
     */
    private function resolveDisks(): array
    {
        $requestedDisks  = array_filter(array_map(
            static fn(mixed $disk): string => trim((string) $disk),
            $this->option('disk'),
        ));

        if ($requestedDisks !== []) {
            return array_values(array_unique($requestedDisks));
        }

        $mediaDisks      = EloquentMedia::query()
            ->select('disk')
            ->distinct()
            ->pluck('disk')
            ->all();

        $configuredDisks = array_filter($mediaDisks, is_string(...));

        return array_values(array_unique([...$configuredDisks, 'public', 'local']));
    }

    private function resolveDiskRoot(string $disk): ?string
    {
        $driver   = config("filesystems.disks.{$disk}.driver");
        $root     = config("filesystems.disks.{$disk}.root");

        if ($driver !== 'local' || ! is_string($root) || $root === '') {
            return null;
        }

        $realPath = realpath($root);

        if ($realPath === false) {
            return null;
        }

        return $realPath;
    }

    /**
     * @return array<string, true>
     */
    private function knownMediaDirectories(string $disk): array
    {
        $directories = [];

        EloquentMedia::query()
            ->where('disk', $disk)
            ->orderBy('id')
            ->each(function (EloquentMedia $media) use (&$directories): void {
                $directory               = trim(PathGeneratorFactory::create($media)->getPath($media), '/');
                $directories[$directory] = true;
            });

        return $directories;
    }

    /**
     * @param array<string, true> $knownDirectories
     *
     * @return array{files: int, bytes: int}
     */
    private function cleanupDisk(string $root, array $knownDirectories, int $cutoffTimestamp, bool $force): array
    {
        $files = 0;
        $bytes = 0;

        foreach ($this->mediaRoots($root) as $mediaRoot) {
            foreach ($this->files($mediaRoot) as $file) {
                if ($file->getMTime() >= $cutoffTimestamp) {
                    continue;
                }

                $relativePath = $this->relativePath($root, $file->getPathname());

                if ($this->belongsToKnownMediaDirectory($relativePath, $knownDirectories)) {
                    continue;
                }

                $size         = $file->getSize();

                if (! $force || @unlink($file->getPathname())) {
                    $files++;
                    $bytes += $size;
                }
            }

            if ($force) {
                $this->deleteEmptyDirectories($mediaRoot);
            }
        }

        return ['files' => $files, 'bytes' => $bytes];
    }

    /**
     * @return list<string>
     */
    private function mediaRoots(string $root): array
    {
        $roots = [];

        foreach (MediaType::cases() as $mediaType) {
            $path = $root . DIRECTORY_SEPARATOR . $mediaType->directory();

            if (is_dir($path)) {
                $roots[] = $path;
            }
        }

        return $roots;
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
     * @param array<string, true> $knownDirectories
     */
    private function belongsToKnownMediaDirectory(string $relativePath, array $knownDirectories): bool
    {
        foreach ($knownDirectories as $directory => $_) {
            if (str_starts_with($relativePath, $directory . '/')) {
                return true;
            }
        }

        return false;
    }

    private function relativePath(string $root, string $path): string
    {
        return str_replace('\\', '/', trim(str_replace($root, '', $path), DIRECTORY_SEPARATOR));
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
