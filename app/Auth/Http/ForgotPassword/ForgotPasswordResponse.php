<?php

declare(strict_types=1);

namespace App\Auth\Http\ForgotPassword;

use App\Auth\Application\UseCases\ForgotPassword\ForgotPasswordOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ForgotPasswordOutput
 */
class ForgotPasswordResponse extends JsonResource
{
    /**
     * @return array{sent: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'sent'    => $this->sent,
            'message' => $this->message,
        ];
    }
}
