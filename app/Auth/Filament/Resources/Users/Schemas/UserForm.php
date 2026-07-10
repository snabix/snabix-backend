<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                        TextInput::make('first_name')
                            ->label('Имя')
                            ->placeholder('Например, Imran')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->autofocus()
                            ->required()
                            ->maxLength(100),

                        TextInput::make('last_name')
                            ->label('Фамилия')
                            ->placeholder('Например, Khan')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->required()
                            ->maxLength(100),

                        TextInput::make('email')
                            ->translateLabel()
                            ->placeholder('user@example.com')
                            ->email()
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone_number')
                            ->label('Телефон')
                            ->tel()
                            ->placeholder('+79991234567')
                            ->prefixIcon(Heroicon::OutlinedPhone)
                            ->maxLength(20),

                        DatePicker::make('date_of_birth')
                            ->label('Дата рождения')
                            ->native(false)
                            ->maxDate(now()),

                        Textarea::make('description')
                            ->label('Описание')
                            ->placeholder('Краткое описание пользователя')
                            ->maxLength(1000)
                            ->columnSpanFull(),
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
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Аккаунт активен')
                            ->default(true),

                        DateTimePicker::make('email_verified_at')
                            ->label('Подтверждено в')
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Оставьте поле пустым, если пользователь ещё не подтвердил email.'),
                    ]),
            ]);
    }
}
