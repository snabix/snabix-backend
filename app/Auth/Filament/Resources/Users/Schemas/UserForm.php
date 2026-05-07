<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Основная информация')
                    ->description('Базовые данные пользователя, которые используются для аутентификации и отображения в системе.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->translateLabel()
                            ->placeholder('Например, Imran')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->autofocus()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->translateLabel()
                            ->placeholder('user@example.com')
                            ->email()
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Безопасность')
                    ->description('Пароль требуется при создании пользователя. При редактировании его можно оставить пустым, если менять не нужно.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->translateLabel()
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->saved(fn(?string $state): bool => filled($state))
                            ->confirmed(),

                        TextInput::make('password_confirmation')
                            ->translateLabel()
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->same('password')
                            ->saved(false),
                    ]),

                Section::make('Статус верификации')
                    ->description('При необходимости администратор может вручную отметить почту пользователя как подтверждённую.')
                    ->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Подтверждено в')
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Оставьте поле пустым, если пользователь ещё не подтвердил email.'),
                    ]),
            ]);
    }
}
