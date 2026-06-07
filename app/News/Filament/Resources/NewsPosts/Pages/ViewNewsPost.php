<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Pages;

use App\News\Filament\Resources\NewsPosts\NewsPostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNewsPost extends ViewRecord
{
    protected static string $resource = NewsPostResource::class;

    protected static ?string $title   = 'Просмотр новости';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Редактировать'),
            DeleteAction::make()
                ->label('Удалить'),
        ];
    }
}
