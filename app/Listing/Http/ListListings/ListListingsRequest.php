<?php

declare(strict_types=1);

namespace App\Listing\Http\ListListings;

use Illuminate\Foundation\Http\FormRequest;

class ListListingsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }
}
