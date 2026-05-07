<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Repositories;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Entities\User;
use App\Auth\Domain\ValueObjects\Name;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\Password;
use App\Shared\Domain\ValueObjects\UUID;
use DateTimeImmutable;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function byId(UUID $id): ?User
    {
        $user = EloquentUser::query()->find($id->value());

        return $user ? $this->toDomain($user) : null;
    }

    public function byEmail(Email $email): ?User
    {
        $user = EloquentUser::query()
            ->where('email', $email->value())
            ->first();

        return $user ? $this->toDomain($user) : null;
    }

    public function existByEmail(Email $email): bool
    {
        return EloquentUser::query()
            ->where('email', $email->value())
            ->exists();
    }

    public function existByEmailExceptUser(Email $email, UUID $userId): bool
    {
        return EloquentUser::query()
            ->where('email', $email->value())
            ->whereKeyNot($userId->value())
            ->exists();
    }

    public function save(User $model): void
    {
        EloquentUser::query()->updateOrCreate(
            ['id' => $model->id->value()],
            [
                'name' => $model->name->value(),
                'email' => $model->email->value(),
                'password' => $model->password->value(),
                'email_verified_at' => $model->emailVerifiedAt?->format('Y-m-d H:i:s'),
            ],
        );
    }

    private function toDomain(EloquentUser $user): User
    {
        return new User(
            id: new UUID((string) $user->getKey()),
            name: new Name($user->name),
            email: new Email($user->email),
            password: new Password($user->password),
            emailVerifiedAt: $user->email_verified_at
                ? new DateTimeImmutable($user->email_verified_at->toDateTimeString())
                : null,
        );
    }
}
