<?php

declare(strict_types=1);

namespace App\Auth\Http\TerminateSession;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class TerminateSessionRequest extends FormRequest
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
     * @return array{userId: string, sessionId: string}
     */
    public function inputData(): array
    {
        $sessionId = $this->route('sessionId');

        return [
            'userId'    => $this->userId(),
            'sessionId' => is_string($sessionId) ? $sessionId : '',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
