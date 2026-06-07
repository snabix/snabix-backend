<?php

declare(strict_types=1);

namespace App\Auth\Http\SignUp;

use App\Auth\Application\UseCases\SignUp\SignUpOutput;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SignUpOutput
 */
class SignUpResponse extends JsonResource
{
    /**
     * @return array{userId: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'userId' => $this->userId,
        ];
    }
}
