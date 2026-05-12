<?php

declare(strict_types=1);

namespace App\Catalog\Filament\Resources\Categories\Pages;

use App\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Catalog\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

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
        $recordId = $record->getKey();

        return app(CategoryRepositoryInterface::class)->save(
            $data,
            is_int($recordId) ? $recordId : null,
        );
    }
}
