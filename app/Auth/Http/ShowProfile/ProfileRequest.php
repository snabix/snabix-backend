<?php

declare(strict_types=1);

namespace App\Auth\Http\ShowProfile;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{userId: string}
     */
    public function inputData(): array
    {
        return [
            'userId' => $this->userId(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
