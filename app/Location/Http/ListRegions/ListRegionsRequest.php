<?php

declare(strict_types=1);

namespace App\Location\Http\ListRegions;

use Illuminate\Foundation\Http\FormRequest;

class ListRegionsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{}
     */
    public function inputData(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }
}
