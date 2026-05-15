<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Models;

use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                                   $id
 * @property string                                $listing_id
 * @property int                                   $attribute_definition_id
 * @property array<string, mixed>|list<mixed>|null $value
 * @property string|null                           $display_value
 */
class EloquentListingAttributeValue extends Model
{
    protected $table    = 'listing_attribute_values';

    /** @var list<string> */
    protected $fillable = [
        'listing_id',
        'attribute_definition_id',
        'value',
        'display_value',
    ];

    /**
     * @return BelongsTo<EloquentListing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(EloquentListing::class, 'listing_id');
    }

    /**
     * @return BelongsTo<EloquentCategoryAttributeDefinition, $this>
     */
    public function attributeDefinition(): BelongsTo
    {
        return $this->belongsTo(EloquentCategoryAttributeDefinition::class, 'attribute_definition_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value'      => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
