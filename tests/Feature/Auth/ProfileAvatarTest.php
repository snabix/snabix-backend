<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Application\Services\UserAvatarService;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Media\Domain\Enums\MediaType;
use App\Media\Domain\Enums\MediaVisibility;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\FeatureTestCase;

class ProfileAvatarTest extends FeatureTestCase
{
    public function test_authenticated_user_can_upload_profile_avatar(): void
    {
        Storage::fake('public');

        $user     = EloquentUser::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/me/avatar', [
                'avatar' => $this->fakeImage('avatar.jpg'),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.avatar.fileName', 'avatar.jpg')
            ->assertJsonPath('data.avatar.mimeType', 'image/png');

        $media    = EloquentMedia::query()
            ->where('model_type', EloquentUser::class)
            ->where('model_id', $user->id)
            ->where('collection_name', UserAvatarService::COLLECTION_NAME)
            ->first();

        $this->assertInstanceOf(EloquentMedia::class, $media);
        $this->assertSame(MediaType::IMAGE, $media->media_type);
        $this->assertSame(MediaVisibility::PUBLIC, $media->visibility);
        Storage::disk('public')->assertExists($this->expectedAvatarPath($media, 'avatar.jpg'));
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.profile.avatar.update',
            'user_id'  => $user->id,
        ]);
    }

    public function test_authenticated_user_can_replace_profile_avatar_and_old_file_is_removed(): void
    {
        Storage::fake('public');

        $user     = EloquentUser::factory()->create();

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/me/avatar', [
                'avatar' => $this->fakeImage('old-avatar.jpg'),
            ])
            ->assertOk();

        $media    = EloquentMedia::query()->where('model_id', $user->id)->firstOrFail();
        $oldPath  = $this->expectedAvatarPath($media, 'old-avatar.jpg');

        Storage::disk('public')->assertExists($oldPath);

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/me/avatar', [
                'avatar' => $this->fakeImage('new-avatar.png'),
            ])
            ->assertOk()
            ->assertJsonPath('data.avatar.fileName', 'new-avatar.png')
            ->assertJsonPath('data.avatar.mimeType', 'image/png');

        $newMedia = EloquentMedia::query()->where('model_id', $user->id)->firstOrFail();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($this->expectedAvatarPath($newMedia, 'new-avatar.png'));
        $this->assertSame(1, EloquentMedia::query()->where('model_id', $user->id)->count());
    }

    public function test_authenticated_user_can_delete_profile_avatar(): void
    {
        Storage::fake('public');

        $user      = EloquentUser::factory()->create();

        $this
            ->actingAs($user)
            ->postJson('/api/v1/auth/me/avatar', [
                'avatar' => $this->fakeImage('avatar.jpg'),
            ])
            ->assertOk();

        $media     = EloquentMedia::query()->where('model_id', $user->id)->firstOrFail();
        $mediaPath = $this->expectedAvatarPath($media, 'avatar.jpg');

        Storage::disk('public')->assertExists($mediaPath);

        $this
            ->actingAs($user)
            ->deleteJson('/api/v1/auth/me/avatar')
            ->assertOk()
            ->assertJsonPath('data.avatar', null);

        Storage::disk('public')->assertMissing($mediaPath);
        $this->assertDatabaseMissing('media', [
            'id' => $media->id,
        ]);
        $this->assertDatabaseHas('system_logs', [
            'category' => 'auth',
            'action'   => 'auth.profile.avatar.delete',
            'user_id'  => $user->id,
        ]);
    }

    private function expectedAvatarPath(EloquentMedia $media, string $fileName): string
    {
        return MediaType::IMAGE->directory() . '/avatar/' . $media->uuid . '/' . $fileName;
    }

    private function fakeImage(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', true) ?: '',
        );
    }
}
