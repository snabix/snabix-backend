<?php

declare(strict_types=1);

namespace App\Auth\Http\UpdateProfile;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UpdateProfileRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, string|Unique>>
     */
    public function rules(): array
    {
        return [
            'firstName'   => ['present', 'nullable', 'string', 'min:2', 'max:100'],
            'lastName'    => ['present', 'nullable', 'string', 'min:2', 'max:100'],
            'email'       => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->userId()),
            ],
            'phoneNumber' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'dateOfBirth' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function inputData(): array
    {
        return [
            'userId'      => $this->userId(),
            'firstName'   => $this->nullableStringInput('firstName'),
            'lastName'    => $this->nullableStringInput('lastName'),
            'email'       => $this->string('email')->toString(),
            'phoneNumber' => $this->filled('phoneNumber')
                ? $this->string('phoneNumber')->toString()
                : null,
            'description' => $this->filled('description')
                ? $this->string('description')->trim()->toString()
                : null,
            'dateOfBirth' => $this->filled('dateOfBirth')
                ? $this->string('dateOfBirth')->toString()
                : null,
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    private function nullableStringInput(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->trim()->toString() : null;
    }
}
