<?php

declare(strict_types=1);

namespace App\Auth\Http\ListActiveSessions;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class ListActiveSessionsRequest extends FormRequest
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
     * @return array{userId: string, currentSessionId: ?string}
     */
    public function inputData(): array
    {
        return [
            'userId'           => $this->userId(),
            'currentSessionId' => $this->currentSessionId(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    private function currentSessionId(): ?string
    {
        return $this->hasSession()
            ? $this->session()->getId()
            : null;
    }
}
