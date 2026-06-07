<?php

declare(strict_types=1);

namespace App\News\Http\ShowPublishedNewsPost;

use Illuminate\Foundation\Http\FormRequest;

class ShowPublishedNewsPostRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function slug(): string
    {
        $slug = $this->route('slug');

        return is_string($slug) ? $slug : '';
    }

    /**
     * @return array{slug: string}
     */
    public function inputData(): array
    {
        return [
            'slug' => $this->slug(),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
