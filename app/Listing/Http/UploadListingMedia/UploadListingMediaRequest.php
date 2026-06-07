<?php

declare(strict_types=1);

namespace App\Listing\Http\UploadListingMedia;

use App\Shared\Http\Requests\ResolvesAuthenticatedUserId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UploadListingMediaRequest extends FormRequest
{
    use ResolvesAuthenticatedUserId;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'images'   => ['required', 'array', 'min:1', 'max:8'],
            'images.*' => ['required', 'file', 'image', 'max:3072'],
        ];
    }

    public function listingId(): string
    {
        $listingId = $this->route('listingId');

        return is_string($listingId) ? $listingId : '';
    }

    /**
     * @return array{userId: string, listingId: string, images: list<UploadedFile>}
     */
    public function inputData(): array
    {
        return [
            'userId'    => $this->userId(),
            'listingId' => $this->listingId(),
            'images'    => $this->imageFiles(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return list<UploadedFile>
     */
    private function imageFiles(): array
    {
        return array_values(collect($this->file('images'))
            ->filter(fn(mixed $file): bool => $file instanceof UploadedFile)
            ->values()
            ->all());
    }
}
