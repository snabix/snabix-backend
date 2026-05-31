<?php

declare(strict_types=1);

namespace App\Listing\Http\ReorderListingMedia;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class ReorderListingMediaRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'mediaIds'   => ['required', 'array', 'min:1', 'max:8'],
            'mediaIds.*' => ['required', 'integer', 'distinct', 'min:1'],
        ];
    }

    /**
     * @return array{userId: string, listingId: string, mediaIds: list<int>}
     */
    public function inputData(): array
    {
        return [
            'userId'    => $this->userId(),
            'listingId' => $this->listingId(),
            'mediaIds'  => $this->mediaIds(),
        ];
    }

    public function listingId(): string
    {
        $listingId = $this->route('listingId');

        return is_string($listingId) ? $listingId : '';
    }

    /**
     * @return list<int>
     */
    public function mediaIds(): array
    {
        $mediaIds         = $this->input('mediaIds');

        if (! is_array($mediaIds)) {
            return [];
        }

        $resolvedMediaIds = [];

        foreach ($mediaIds as $mediaId) {
            if (is_int($mediaId)) {
                $resolvedMediaIds[] = $mediaId;

                continue;
            }

            if (is_string($mediaId) && ctype_digit($mediaId)) {
                $resolvedMediaIds[] = (int) $mediaId;
            }
        }

        return $resolvedMediaIds;
    }

    public function authorize(): bool
    {
        return true;
    }
}
