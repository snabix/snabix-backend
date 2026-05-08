<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Pages;

use App\Media\Application\Services\MediaStorageService;
use App\Media\Filament\Resources\Media\MediaResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected static ?string $title = 'Загрузить медиафайл';

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $uploadedPath = $this->extractUploadedPath($data['uploaded_file'] ?? null);
        unset($data['uploaded_file']);

        $data = $this->normalizeAttachment($data);
        $data['uploaded_by_admin_id'] = Filament::auth()->id();

        /** @var MediaStorageService $storage */
        $storage = app(MediaStorageService::class);

        return $storage->createFromStoredUpload('local', (string) $uploadedPath, $data);
    }

    protected function getRedirectUrl(): string
    {
        return MediaResource::getUrl('index');
    }

    private function extractUploadedPath(mixed $state): ?string
    {
        $path = Arr::first(Arr::wrap($state));

        return is_string($path) && $path !== '' ? $path : null;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeAttachment(array $data): array
    {
        if (blank($data['model_type'] ?? null) || blank($data['model_id'] ?? null)) {
            $data['model_type'] = null;
            $data['model_id'] = null;
        }

        return $data;
    }
}
