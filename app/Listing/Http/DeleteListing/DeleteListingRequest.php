<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListing;

use Illuminate\Foundation\Http\FormRequest;

class DeleteListingRequest extends FormRequest
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
