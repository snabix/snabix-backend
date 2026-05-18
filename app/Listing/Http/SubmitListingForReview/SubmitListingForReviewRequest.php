<?php

declare(strict_types=1);

namespace App\Listing\Http\SubmitListingForReview;

use Illuminate\Foundation\Http\FormRequest;

class SubmitListingForReviewRequest extends FormRequest
{
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

    public function userId(): string
    {
        $user       = $this->user();
        $identifier = is_object($user) ? $user->getAuthIdentifier() : null;

        return is_string($identifier) || is_int($identifier)
            ? (string) $identifier
            : '';
    }

    public function authorize(): bool
    {
        return true;
    }
}
