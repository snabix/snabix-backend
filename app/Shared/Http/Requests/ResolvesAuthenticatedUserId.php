<?php

declare(strict_types=1);

namespace App\Shared\Http\Requests;

trait ResolvesAuthenticatedUserId
{
    public function userId(): string
    {
        $user       = $this->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
    }
}
