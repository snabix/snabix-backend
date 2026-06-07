<?php

declare(strict_types=1);

namespace App\Listing\Http\DeleteListingMedia;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class DeleteListingMediaRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array{userId: string, listingId: string, mediaId: int}
     */
    public function inputData(): array
    {
        return [
            'userId'    => $this->userId(),
            'listingId' => $this->listingId(),
            'mediaId'   => $this->mediaId(),
        ];
    }

    public function listingId(): string
    {
        $listingId = $this->route('listingId');

        return is_string($listingId) ? $listingId : '';
    }

    public function mediaId(): int
    {
        $mediaId = $this->route('mediaId');

        return is_numeric($mediaId) ? (int) $mediaId : 0;
    }

    public function authorize(): bool
    {
        return true;
    }
}
