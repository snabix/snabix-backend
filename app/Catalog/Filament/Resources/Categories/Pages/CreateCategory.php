<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

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
        return app(CategoryRepositoryInterface::class)->save($data);
    }
}
