<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListing;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class DeleteListingRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function listingId(): string
    {
        $listingId = $this->route('listingId');

        return is_string($listingId) ? $listingId : '';
    }

    /**
     * @return array{userId: string, listingId: string}
     */
    public function inputData(): array
    {
        return [
            'userId'    => $this->userId(),
            'listingId' => $this->listingId(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
