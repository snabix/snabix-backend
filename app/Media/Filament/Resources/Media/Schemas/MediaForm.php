<?php

declare(strict_types=1);

namespace App\Media\Filament\Resources\Media\Schemas;

use App\Media\Application\Support\MediaAttachableModels;
use App\Media\Application\Support\MediaTypeDetector;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Файл')
                    ->description('Загрузите новый файл. При редактировании загрузка нового файла заменит старый файл в хранилище.')
                    ->schema([
                        FileUpload::make('uploaded_file')
                            ->label('Файл')
                            ->disk('local')
                            ->directory('filament-media-temp')
                            ->preserveFilenames()
                            ->storeFiles()
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->afterStateUpdated(fn(mixed $state, Set $set): null => self::fillMetadataFromUpload($state, $set))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxSize(1024 * 3)
                            ->helperText('До 3 МБ. Расширение может быть любым: svg, png, jpg, jpeg, pdf, docx и другие.'),
                    ]),

                Section::make('Описание')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->prefixIcon(Heroicon::OutlinedDocumentText)
                            ->required()
                            ->maxLength(255),

                        TextInput::make('collection_name')
                            ->label('Коллекция')
                            ->default('default')
                            ->required()
                            ->maxLength(255),

                        Select::make('media_type')
                            ->label('Тип')
                            ->options(MediaType::options())
                            ->default(MediaType::FILE->value)
                            ->required()
                            ->live()
                            ->native(false),

                        Placeholder::make('target_directory')
                            ->label('Будущая директория')
                            ->content(fn(Get $get): string => self::directoryPreview($get('media_type'))),

                        Select::make('visibility')
                            ->label('Доступ')
                            ->options(MediaVisibility::options())
                            ->default(MediaVisibility::PUBLIC->value)
                            ->required()
                            ->native(false),

                        Select::make('disk')
                            ->label('Диск')
                            ->options([
                                'public' => 'public',
                                'local'  => 'private/local',
                            ])
                            ->default('public')
                            ->required()
                            ->native(false)
                            ->helperText('Для публичных файлов используйте public, для приватных — local.'),

                        Textarea::make('description')
                            ->label('Описание')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Привязка')
                    ->description('Файл может быть не привязан ни к одной записи или связан с разрешенной моделью.')
                    ->columns(2)
                    ->schema([
                        Select::make('model_type')
                            ->label('Модель')
                            ->options(MediaAttachableModels::options())
                            ->live()
                            ->native(false)
                            ->placeholder('Без привязки'),

                        Select::make('model_id')
                            ->label('Запись')
                            ->options(function (Get $get): array {
                                $modelClass = $get('model_type');

                                return MediaAttachableModels::recordOptions(
                                    is_string($modelClass) ? $modelClass : null,
                                );
                            })
                            ->native(false)
                            ->searchable()
                            ->placeholder('Выберите запись')
                            ->disabled(fn(Get $get): bool => blank($get('model_type'))),
                    ]),
            ]);
    }

    private static function fillMetadataFromUpload(mixed $state, Set $set): null
    {
        $file         = Arr::first(Arr::wrap($state));

        if (! $file instanceof TemporaryUploadedFile) {
            return null;
        }

        $originalName = $file->getClientOriginalName();
        $extension    = $file->getClientOriginalExtension() ?: null;
        $mimeType     = $file->getMimeType();

        /** @var MediaTypeDetector $detector */
        $detector     = app(MediaTypeDetector::class);
        $mediaType    = $detector->detect($mimeType, $extension);

        $set('name', pathinfo($originalName, PATHINFO_FILENAME) ?: $originalName);
        $set('media_type', $mediaType->value, shouldCallUpdatedHooks: true);
        $set('collection_name', $mediaType->directory());
        $set('visibility', MediaVisibility::PUBLIC->value);
        $set('disk', MediaVisibility::PUBLIC->disk());

        return null;
    }

    private static function directoryPreview(mixed $mediaType): string
    {
        if (! is_numeric($mediaType)) {
            return MediaType::FILE->directory() . '/{collection}/{media_uuid}/';
        }

        return MediaType::from((int) $mediaType)->directory() . '/{collection}/{media_uuid}/';
    }
}
