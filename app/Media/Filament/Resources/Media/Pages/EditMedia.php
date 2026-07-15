<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Pages;

use App\Media\Application\Services\MediaStorageService;
use App\Media\Filament\Resources\Media\MediaResource;
use App\Media\Filament\Resources\Media\Schemas\MediaForm;
use App\Media\Infrastructure\Models\EloquentMedia;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected static ?string $title   = 'Редактировать медиафайл';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Просмотр'),
            DeleteAction::make()
                ->label('Удалить'),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var EloquentMedia $record */
        $record                = $this->record;
        $data['uploaded_file'] = [MediaForm::existingMediaState($record)];

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $uploadedPath = $this->extractUploadedPath($data['uploaded_file'] ?? null);
        unset($data['uploaded_file']);

        $data         = $this->normalizeAttachment($data);

        /** @var EloquentMedia $record */
        /** @var MediaStorageService $storage */
        $storage      = app(MediaStorageService::class);

        if ($uploadedPath !== null) {
            $record = $storage->replaceFromStoredUpload($record, 'local', $uploadedPath, [
                ...$data,
                'visibility' => $record->visibility,
                'disk'       => $record->disk,
            ]);

            $record = $storage->updateMetadata($record, $data);
        } else {
            $record = $storage->updateMetadata($record, $data);
        }

        return $record;
    }

    private function extractUploadedPath(mixed $state): ?string
    {
        $path = Arr::first(Arr::wrap($state));

        if (! is_string($path) || $path === '' || MediaForm::isExistingMediaState($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeAttachment(array $data): array
    {
        if (blank($data['model_type'] ?? null) || blank($data['model_id'] ?? null)) {
            $data['model_type'] = null;
            $data['model_id']   = null;
        }

        return $data;
    }
}
