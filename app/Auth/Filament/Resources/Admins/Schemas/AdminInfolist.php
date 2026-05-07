<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins\Schemas;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Профиль')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->translateLabel(),

                        TextEntry::make('email')
                            ->translateLabel()
                            ->copyable(),

                        IconEntry::make('email_verified_at')
                            ->translateLabel()
                            ->boolean()
                            ->state(fn(EloquentAdmin $record): bool => $record->email_verified_at !== null),

                        TextEntry::make('email_verified_at')
                            ->translateLabel()
                            ->placeholder('Ещё не подтверждено')
                            ->dateTime('d.m.Y H:i'),
                    ]),

                Section::make('Системная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->translateLabel()
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->translateLabel()
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('updated_at')
                            ->translateLabel()
                            ->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
