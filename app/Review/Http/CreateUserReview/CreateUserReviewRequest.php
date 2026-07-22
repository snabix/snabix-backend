<?php

declare(strict_types=1);

namespace App\Review\Http\CreateUserReview;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use App\Shared\Http\Requests\ResolvesIdempotencyKey;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserReviewRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;
    use ResolvesIdempotencyKey;

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'listingId' => ['required', 'uuid'],
            'rating'    => ['required', 'integer', 'min:1', 'max:5'],
            'comment'   => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function authorize(): true
    {
        return true;
    }

    /**
     * @return array{listingId: string, rating: int, comment: string|null}
     */
    public function inputData(): array
    {
        return [
            'listingId' => $this->stringInput('listingId'),
            'rating'    => $this->integerInput('rating'),
            'comment'   => $this->nullableString('comment'),
        ];
    }

    private function stringInput(string $key): string
    {
        $value = $this->validated($key);

        return is_string($value) ? $value : '';
    }

    private function integerInput(string $key): int
    {
        $value = $this->validated($key);

        return is_int($value) || is_numeric($value) ? (int) $value : 0;
    }

    private function nullableString(string $key): ?string
    {
        $value = $this->validated($key);

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : null;
    }
}
