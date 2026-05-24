<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Number;

/**
 * @property      int                           $id
 * @property      string                        $kladr_id
 * @property      string|null                   $fias_guid
 * @property      string                        $name
 * @property      string                        $slug
 * @property      string                        $label
 * @property      string|null                   $type
 * @property      string|null                   $type_short
 * @property      string                        $content_type
 * @property      string|null                   $okato
 * @property      string|null                   $oktmo
 * @property      string|null                   $code
 * @property      string|null                   $iso_code
 * @property      int|null                      $population
 * @property      int|null                      $year_founded
 * @property      int|null                      $area
 * @property      string|null                   $fullname
 * @property      string|null                   $unofficial_name
 * @property      string|null                   $name_en
 * @property      string|null                   $district
 * @property      array<string, string>|null    $name_cases
 * @property      array<string, mixed>|null     $capital_data
 * @property      bool                          $is_active
 * @property      int                           $sort_order
 * @property-read Collection<int, EloquentCity> $cities
 * @property-read string                        $display_name
 * @property-read string|null                   $human_readable_population
 */
class EloquentRegion extends Model
{
    protected $table    = 'regions';

    /** @var list<string> */
    protected $fillable = [
        'kladr_id',
        'fias_guid',
        'name',
        'slug',
        'label',
        'type',
        'type_short',
        'content_type',
        'okato',
        'oktmo',
        'code',
        'iso_code',
        'population',
        'year_founded',
        'area',
        'fullname',
        'unofficial_name',
        'name_en',
        'district',
        'name_cases',
        'capital_data',
        'is_active',
        'sort_order',
    ];

    /**
     * @return HasMany<EloquentCity, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(EloquentCity::class, 'region_id')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'population'  => 'integer',
            'year_founded'=> 'integer',
            'area'        => 'integer',
            'name_cases'  => 'array',
            'capital_data'=> 'array',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn(): string => $this->fullname ?: $this->name);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function humanReadablePopulation(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->population === null) {
                return null;
            }

            $formatted = Number::format($this->population, locale: 'ru');

            return is_string($formatted) ? $formatted : (string) $this->population;
        });
    }
}
