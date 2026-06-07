<?php

declare(strict_types=1);

namespace App\Auth\Http\ChangePassword;

use App\Auth\Application\UseCases\ChangePassword\ChangePasswordOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ChangePasswordOutput
 */
class ChangePasswordResponse extends JsonResource
{
    /**
     * @return array{changed: bool, message: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'changed' => $this->changed,
            'message' => $this->message,
        ];
    }
}
