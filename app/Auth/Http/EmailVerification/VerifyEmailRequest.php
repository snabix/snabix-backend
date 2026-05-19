<?php

declare(strict_types=1);

namespace App\Auth\Http\EmailVerification;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function inputData(): array
    {
        return [
            'userId' => $this->userId(),
            'code'   => $this->string('code')->toString(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
