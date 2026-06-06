<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Pages;

use App\News\Filament\Resources\NewsPosts\NewsPostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNewsPost extends EditRecord
{
    protected static string $resource = NewsPostResource::class;

    protected static ?string $title   = 'Редактировать новость';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Просмотр'),
            DeleteAction::make()
                ->label('Удалить'),
        ];
    }
}
