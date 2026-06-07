<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Models;

use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Listing\Infrastructure\Models\EloquentListingAttributeValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int                                         $id
 * @property string                                      $category_id
 * @property string                                      $name
 * @property string                                      $slug
 * @property CategoryAttributeType                       $type
 * @property string|null                                 $unit
 * @property string|null                                 $description
 * @property string|null                                 $placeholder
 * @property string|null                                 $help_text
 * @property array<int, mixed>|array<string, mixed>|null $default_value
 * @property array<int, mixed>|array<string, mixed>|null $dependency_rules
 * @property string|null                                 $group_name
 * @property array<int, mixed>|null                      $options
 * @property bool                                        $is_required
 * @property bool                                        $is_filterable
 * @property bool                                        $show_in_card
 * @property bool                                        $is_active
 * @property bool                                        $applies_to_children
 * @property int                                         $schema_version
 * @property int                                         $sort_order
 */
class EloquentCategoryAttributeDefinition extends Model
{
    protected $table    = 'category_attribute_definitions';

    /** @var list<string> */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'type',
        'unit',
        'description',
        'placeholder',
        'help_text',
        'default_value',
        'dependency_rules',
        'group_name',
        'options',
        'is_required',
        'is_filterable',
        'show_in_card',
        'is_active',
        'applies_to_children',
        'schema_version',
        'sort_order',
    ];

    /**
     * @return BelongsTo<EloquentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EloquentCategory::class, 'category_id');
    }

    /**
     * @return HasMany<EloquentListingAttributeValue, $this>
     */
    public function listingValues(): HasMany
    {
        return $this->hasMany(EloquentListingAttributeValue::class, 'attribute_definition_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'               => CategoryAttributeType::class,
            'default_value'      => 'array',
            'dependency_rules'   => 'array',
            'options'            => 'array',
            'is_required'        => 'boolean',
            'is_filterable'      => 'boolean',
            'show_in_card'       => 'boolean',
            'is_active'          => 'boolean',
            'applies_to_children'=> 'boolean',
            'schema_version'     => 'integer',
            'sort_order'         => 'integer',
            'created_at'         => 'datetime',
            'updated_at'         => 'datetime',
        ];
    }
}
