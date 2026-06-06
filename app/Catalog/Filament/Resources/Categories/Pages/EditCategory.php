<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Application\Services\CategoryIconMediaService;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class EditCategory extends EditRecord
{
    private const string CURRENT_ICON_STATE_PREFIX = 'current-media:';

    protected static string $resource              = CategoryResource::class;

    public function getTitle(): string
    {
        return __('Edit category');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->translateLabel(),
            DeleteAction::make()
                ->translateLabel(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $uploadedIconPath = $this->extractUploadedIconPath($data['uploaded_icon'] ?? null);
        unset($data['uploaded_icon']);

        $recordId         = $record->getKey();

        $category         = app(CategoryRepositoryInterface::class)->save(
            $data,
            is_string($recordId) && $recordId !== '' ? $recordId : null,
        );

        if ($uploadedIconPath !== null) {
            app(CategoryIconMediaService::class)->replaceFromStoredUpload($category, 'local', $uploadedIconPath);

            return $category->refresh();
        }

        return $category;
    }

    private function extractUploadedIconPath(mixed $state): ?string
    {
        $path = Arr::first(
            Arr::wrap($state),
            fn(mixed $path): bool => is_string($path)
                && $path !== ''
                && ! str_starts_with($path, self::CURRENT_ICON_STATE_PREFIX),
        );

        return is_string($path) && $path !== '' ? $path : null;
    }
}
