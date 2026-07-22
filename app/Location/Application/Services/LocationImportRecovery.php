<?php

declare(strict_types=1);

namespace App\Location\Application\Services;

use App\Location\Domain\Enums\LocationImportStatus;
use App\Location\Infrastructure\Models\EloquentLocationImportManifest;
use Illuminate\Support\Facades\DB;

class LocationImportRecovery
{
    public function cleanupAbandonedImports(): void
    {
        $staleAfterSeconds = config('location-import.stale_after_seconds', 3600);

        if (! is_int($staleAfterSeconds) || $staleAfterSeconds < 1) {
            $staleAfterSeconds = 3600;
        }

        $staleIds          = EloquentLocationImportManifest::query()
            ->whereIn('status', [
                LocationImportStatus::PREPARING,
                LocationImportStatus::PREVIEW,
            ])
            ->where('started_at', '<', now()->subSeconds($staleAfterSeconds))
            ->pluck('id')
            ->filter(static fn(mixed $id): bool => is_string($id))
            ->values()
            ->all();

        if ($staleIds === []) {
            return;
        }

        DB::transaction(function () use ($staleIds): void {
            DB::table('location_import_staging')
                ->whereIn('manifest_id', $staleIds)
                ->delete();

            EloquentLocationImportManifest::query()
                ->whereIn('id', $staleIds)
                ->update([
                    'status'        => LocationImportStatus::FAILED->value,
                    'error_message' => 'Импорт прерван до завершения; временные данные очищены.',
                    'completed_at'  => now(),
                    'updated_at'    => now(),
                ]);
        });
    }
}
