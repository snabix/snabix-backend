<?php

declare(strict_types=1);

namespace App\News\Http\ListPublishedNewsPosts;

use Illuminate\Foundation\Http\FormRequest;

class ListPublishedNewsPostsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'page'         => ['sometimes', 'integer', 'min:1'],
            'perPage'      => ['sometimes', 'integer', 'min:1', 'max:50'],
            'category'     => ['sometimes', 'string', 'max:120'],
            'featuredOnly' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{page: int, perPage: int, category: string|null, featuredOnly: bool}
     */
    public function inputData(): array
    {
        return [
            'page'         => (int) $this->integer('page', 1),
            'perPage'      => (int) $this->integer('perPage', 12),
            'category'     => $this->string('category')->toString() !== ''
                ? $this->string('category')->toString()
                : null,
            'featuredOnly' => $this->boolean('featuredOnly'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
