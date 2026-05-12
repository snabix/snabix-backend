<?php

declare(strict_types=1);

namespace App\Auth\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Media\Application\Services\MediaStorageService;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Http\UploadedFile;
use Throwable;

readonly class UserAvatarService
{
    public const string COLLECTION_NAME = 'avatar';

    public function __construct(
        private MediaStorageService $mediaStorageService,
    ) {}

    public function findForUser(string $userId): ?EloquentMedia
    {
        return EloquentMedia::query()
            ->where('model_type', EloquentUser::class)
            ->where('model_id', $userId)
            ->where('collection_name', self::COLLECTION_NAME)
            ->latest('id')
            ->first();
    }

    /**
     * @throws Throwable
     */
    public function uploadForUser(
        string       $userId,
        UploadedFile $file,
    ): EloquentMedia {
        $sourcePath    = $file->storeAs(
            'profile-avatar-temp/' . $userId,
            $file->getClientOriginalName(),
            'local',
        );

        if (! is_string($sourcePath) || $sourcePath === '') {
            throw new \RuntimeException('Не удалось сохранить временный файл аватара.');
        }

        $currentAvatar = $this->findForUser($userId);
        $attributes    = [
            'model_type'      => EloquentUser::class,
            'model_id'        => $userId,
            'collection_name' => self::COLLECTION_NAME,
            'name'            => 'user-avatar-' . $userId,
            'media_type'      => MediaType::IMAGE,
            'visibility'      => MediaVisibility::PUBLIC,
            'description'     => 'User profile avatar.',
        ];

        if ($currentAvatar instanceof EloquentMedia) {
            return $this->mediaStorageService->replaceFromStoredUpload($currentAvatar, 'local', $sourcePath, $attributes);
        }

        return $this->mediaStorageService->createFromStoredUpload('local', $sourcePath, $attributes);
    }

    public function deleteForUser(string $userId): void
    {
        $this->findForUser($userId)?->delete();
    }

    /**
     * @return array{id: int, url: ?string, fileName: string, mimeType: ?string, size: int, humanReadableSize: string}|null
     */
    public function toPayload(string $userId): ?array
    {
        $avatar = $this->findForUser($userId);

        if (!$avatar instanceof EloquentMedia) {
            return null;
        }

        return [
            'id'                => $avatar->id,
            'url'               => $avatar->getFullUrl(),
            'fileName'          => $avatar->file_name,
            'mimeType'          => $avatar->mime_type,
            'size'              => $avatar->size,
            'humanReadableSize' => $avatar->human_readable_size,
        ];
    }
}
