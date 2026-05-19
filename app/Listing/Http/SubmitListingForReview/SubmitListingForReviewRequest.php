<?php

declare(strict_types=1);

namespace App\Listing\Http\SubmitListingForReview;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;

class SubmitListingForReviewRequest extends FormRequest
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

    public function authorize(): bool
    {
        return true;
    }
}
