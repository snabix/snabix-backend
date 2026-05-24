<?php

declare(strict_types=1);

namespace App\Auth\Http\VerifyEmail;

use App\Auth\Application\UseCases\EmailVerification\VerifyEmailOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VerifyEmailOutput
 */
class VerifyEmailResponse extends JsonResource
{
    /**
     * @return array{verified: bool, alreadyVerified: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        $message = data_get($this->resource, 'message');

        return [
            'verified'        => (bool) data_get($this->resource, 'verified'),
            'alreadyVerified' => (bool) data_get($this->resource, 'alreadyVerified'),
            'message'         => is_string($message) ? $message : 'Email успешно подтвержден.',
        ];
    }
}
