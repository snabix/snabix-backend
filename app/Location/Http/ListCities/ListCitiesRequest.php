<?php

declare(strict_types=1);

namespace App\Location\Http\ListCities;

use Illuminate\Foundation\Http\FormRequest;

class ListCitiesRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'regionId' => ['required', 'integer', 'min:1', 'exists:regions,id'],
            'search'   => ['nullable', 'string', 'max:120'],
        ];
    }

    public function regionId(): int
    {
        return $this->integer('regionId');
    }

    public function search(): ?string
    {
        return $this->filled('search') ? $this->string('search')->toString() : null;
    }

    /**
     * @return array{regionId: int, search: string|null}
     */
    public function inputData(): array
    {
        return [
            'regionId' => $this->regionId(),
            'search'   => $this->search(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
