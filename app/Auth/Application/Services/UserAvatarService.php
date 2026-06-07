<?php

declare(strict_types=1);

namespace App\Auth\Application\Services;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Http\UploadedFile;

readonly class UserAvatarService
{
    public const string COLLECTION_NAME = 'avatar';

    public function findForUser(string $userId): ?EloquentMedia
    {
        return EloquentUser::query()
            ->with('avatarMedia')
            ->find($userId)
            ?->avatarMedia;
    }

    public function uploadForUser(
        string       $userId,
        UploadedFile $file,
    ): EloquentMedia {
        /** @var EloquentUser $user */
        $user  = EloquentUser::query()->findOrFail($userId);
        $media = $user
            ->addMedia($file)
            ->usingName('user-avatar-' . $userId)
            ->usingFileName($file->getClientOriginalName())
            ->toMediaCollection(self::COLLECTION_NAME, 'public');

        $media->forceFill([
            'description' => 'User profile avatar.',
        ])->save();

        return EloquentMedia::query()
            ->whereKey($media->getKey())
            ->firstOrFail();
    }

    public function deleteForUser(string $userId): void
    {
        EloquentUser::query()->find($userId)?->clearMediaCollection(self::COLLECTION_NAME);
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
