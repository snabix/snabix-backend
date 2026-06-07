<?php

declare(strict_types=1);

namespace App\Auth\Domain\Contracts;

use App\Auth\Domain\Entities\User;
use App\Shared\Domain\ValueObjects\Email;
use App\Shared\Domain\ValueObjects\UUID;

interface UserRepositoryInterface
{
    public function byId(UUID $id): ?User;

    public function byEmail(Email $email): ?User;

    public function existByEmail(Email $email): bool;

    public function existByEmailExceptUser(Email $email, UUID $userId): bool;

    public function save(User $model): void;
}
