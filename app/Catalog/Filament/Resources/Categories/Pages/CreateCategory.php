<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Application\Services\CategoryIconMediaService;
use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateCategory extends CreateRecord
{
    private const string CURRENT_ICON_STATE_PREFIX = 'current-media:';

    protected static string $resource              = CategoryResource::class;

    public function getTitle(): string
    {
        return __('Create category');
    }

    protected function getRedirectUrl(): string
    {
        return CategoryResource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $uploadedIconPath = $this->extractUploadedIconPath($data['uploaded_icon'] ?? null);
        unset($data['uploaded_icon']);

        $category         = app(CategoryRepositoryInterface::class)->save($data);

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
