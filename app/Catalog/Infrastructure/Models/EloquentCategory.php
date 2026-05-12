<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Models;

use Database\Factories\EloquentCategoryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property      int                               $id
 * @property      int|null                          $parent_id
 * @property      string                            $name
 * @property      string                            $slug
 * @property      string|null                       $description
 * @property      int                               $sort_order
 * @property      bool                              $is_active
 * @property      string|null                       $path
 * @property      int                               $depth
 * @property-read string                            $full_name
 * @property-read EloquentCategory|null             $parentCategory
 * @property-read Collection<int, EloquentCategory> $children
 */
class EloquentCategory extends Model
{
    /** @use HasFactory<EloquentCategoryFactory> */
    use HasFactory;

    protected $table    = 'categories';

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
        'path',
        'depth',
    ];

    /**
     * @return Factory<EloquentCategory>
     */
    protected static function newFactory(): Factory
    {
        return EloquentCategoryFactory::new();
    }

    /**
     * @return BelongsTo<EloquentCategory, $this>
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<EloquentCategory, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
            'depth'      => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<non-falsy-string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            $segments = [$this->name];
            $parent   = $this->parentCategory()->first();

            while ($parent !== null) {
                array_unshift($segments, $parent->name);
                $parent = $parent->parentCategory()->first();
            }

            return implode(' / ', $segments);
        });
    }
}
