<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Pages;

use App\News\Filament\Resources\NewsPosts\NewsPostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNewsPosts extends ListRecords
{
    protected static string $resource = NewsPostResource::class;

    protected static ?string $title   = 'Новости';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Создать новость'),
        ];
    }
}
