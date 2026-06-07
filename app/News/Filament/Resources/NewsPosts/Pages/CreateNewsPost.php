<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Pages;

use App\News\Filament\Resources\NewsPosts\NewsPostResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsPost extends CreateRecord
{
    protected static string $resource = NewsPostResource::class;

    protected static ?string $title   = 'Создать новость';

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['author_admin_id'] ??= Filament::auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return NewsPostResource::getUrl('index');
    }
}
