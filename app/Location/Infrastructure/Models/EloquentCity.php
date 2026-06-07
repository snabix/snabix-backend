<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Number;

/**
 * @property      int                        $id
 * @property      int                        $region_id
 * @property      string                     $kladr_id
 * @property      string|null                $fias_guid
 * @property      string                     $name
 * @property      string|null                $name_alt
 * @property      string                     $slug
 * @property      string                     $label
 * @property      string|null                $type
 * @property      string|null                $type_short
 * @property      string                     $content_type
 * @property      string|null                $okato
 * @property      string|null                $oktmo
 * @property      int|null                   $zip
 * @property      int|null                   $population
 * @property      string|null                $year_founded
 * @property      string|null                $year_city_status
 * @property      string|null                $name_en
 * @property      array<string, string>|null $name_cases
 * @property      string|null                $lat
 * @property      string|null                $lon
 * @property      array<string, string>|null $timezone
 * @property      bool                       $is_capital
 * @property      bool                       $is_dual_name
 * @property      bool                       $is_active
 * @property      int                        $sort_order
 * @property-read EloquentRegion             $region
 * @property-read string|null                $human_readable_population
 * @property-read string|null                $coordinates
 */
class EloquentCity extends Model
{
    protected $table    = 'cities';

    /** @var list<string> */
    protected $fillable = [
        'region_id',
        'kladr_id',
        'fias_guid',
        'name',
        'name_alt',
        'slug',
        'label',
        'type',
        'type_short',
        'content_type',
        'okato',
        'oktmo',
        'zip',
        'population',
        'year_founded',
        'year_city_status',
        'name_en',
        'name_cases',
        'lat',
        'lon',
        'timezone',
        'is_capital',
        'is_dual_name',
        'is_active',
        'sort_order',
    ];

    /**
     * @return BelongsTo<EloquentRegion, $this>
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(EloquentRegion::class, 'region_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'region_id'       => 'integer',
            'zip'             => 'integer',
            'population'      => 'integer',
            'name_cases'      => 'array',
            'timezone'        => 'array',
            'is_capital'      => 'boolean',
            'is_dual_name'    => 'boolean',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer',
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
        ];
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

    /**
     * @return Attribute<non-falsy-string|null, never>
     */
    protected function coordinates(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->lat === null || $this->lon === null) {
                return null;
            }

            return $this->lat . ', ' . $this->lon;
        });
    }
}
