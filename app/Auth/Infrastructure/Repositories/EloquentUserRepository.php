<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Repositories;

use App\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Auth\Domain\Entities\User;
use App\Auth\Domain\ValueObjects\FirstName;
use App\Auth\Domain\ValueObjects\LastName;
use App\Auth\Domain\ValueObjects\PhoneNumber;
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
                'first_name'        => $model->firstName?->value(),
                'last_name'         => $model->lastName?->value(),
                'email'             => $model->email->value(),
                'password'          => $model->password->value(),
                'phone_number'      => $model->phoneNumber?->value(),
                'description'       => $model->description,
                'date_of_birth'     => $model->dateOfBirth,
                'is_active'         => $model->isActive(),
                'email_verified_at' => $model->emailVerifiedAt?->format('Y-m-d H:i:s'),
            ],
        );
    }

    private function toDomain(EloquentUser $user): User
    {
        $userId = $user->getKey();

        return new User(
            id: new UUID(is_string($userId) || is_int($userId) ? (string) $userId : ''),
            firstName: filled($user->first_name) ? new FirstName($user->first_name) : null,
            lastName: filled($user->last_name) ? new LastName($user->last_name) : null,
            email: new Email($user->email),
            password: new Password($user->password),
            isActive: $user->is_active,
            phoneNumber: filled($user->phone_number) ? new PhoneNumber($user->phone_number) : null,
            description: filled($user->description) ? (string) $user->description : null,
            dateOfBirth: $user->date_of_birth?->format('Y-m-d'),
            emailVerifiedAt: $user->email_verified_at
                ? new DateTimeImmutable($user->email_verified_at->toDateTimeString())
                : null,
        );
    }
}
