<?php

declare(strict_types=1);

namespace App\Listing\Http\ListFavoriteListings;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class ListFavoriteListingsRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page'    => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:48'],
        ];
    }

    /**
     * @return array{userId: string, page: int, perPage: int}
     */
    public function inputData(): array
    {
        return [
            'userId'  => $this->userId(),
            'page'    => $this->integer('page', 1),
            'perPage' => $this->integer('perPage', 12),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
