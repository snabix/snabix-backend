<?php

declare(strict_types=1);

namespace App\Auth\Http\ResetPassword;

use App\Auth\Application\UseCases\ResetPassword\ResetPasswordOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ResetPasswordOutput
 */
class ResetPasswordResponse extends JsonResource
{
    /**
     * @return array{reset: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'reset'   => $this->reset,
            'message' => $this->message,
        ];
    }
}
