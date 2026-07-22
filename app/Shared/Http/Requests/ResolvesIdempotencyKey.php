<?php

declare(strict_types=1);

namespace App\Shared\Http\Requests;

use Illuminate\Validation\ValidationException;

trait ResolvesIdempotencyKey
{
    /**
     * @throws ValidationException
     */
    public function idempotencyKey(): ?string
    {
        $value = $this->header('Idempotency-Key');

        if ($value === null) {
            return null;
        }

        if (
            preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{7,127}\z/D', $value) !== 1
        ) {
            throw ValidationException::withMessages([
                'idempotencyKey' => [
                    'Idempotency-Key должен содержать от 8 до 128 латинских букв, цифр или символов . _ : -.',
                ],
            ]);
        }

        return $value;
    }
}
