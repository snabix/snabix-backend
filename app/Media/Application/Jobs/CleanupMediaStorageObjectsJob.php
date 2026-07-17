<?php

declare(strict_types=1);

namespace App\Media\Application\Jobs;

use App\Media\Application\Data\MediaStorageObject;
use App\Media\Application\Services\MediaStorageCleaner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupMediaStorageObjectsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries   = 5;

    public int $timeout = 30;

    /**
     * @param list<array{disk: string, path: string}> $objects
     */
    public function __construct(
        public array $objects,
    ) {
        $queue = config('media-library.cleanup_queue_name', 'media-maintenance');

        if (is_string($queue) && $queue !== '') {
            $this->onQueue($queue);
        }
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function handle(MediaStorageCleaner $cleaner): void
    {
        foreach ($this->objects as $object) {
            $cleaner->deleteIfUnreferenced(new MediaStorageObject(
                disk: $object['disk'],
                path: $object['path'],
            ));
        }
    }
}
