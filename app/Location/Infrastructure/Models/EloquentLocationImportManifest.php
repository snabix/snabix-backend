<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Models;

use App\Location\Domain\Enums\LocationImportStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string                  $id
 * @property string                  $source
 * @property string                  $source_version
 * @property string                  $regions_file
 * @property string                  $cities_file
 * @property string                  $regions_checksum
 * @property string                  $cities_checksum
 * @property int                     $regions_size_bytes
 * @property int                     $cities_size_bytes
 * @property bool                    $fresh
 * @property LocationImportStatus    $status
 * @property array<string, int>|null $stats
 * @property string|null             $error_message
 * @property Carbon                  $started_at
 * @property Carbon|null             $completed_at
 */
class EloquentLocationImportManifest extends Model
{
    public $incrementing = false;

    protected $table     = 'location_import_manifests';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'id',
        'source',
        'source_version',
        'regions_file',
        'cities_file',
        'regions_checksum',
        'cities_checksum',
        'regions_size_bytes',
        'cities_size_bytes',
        'fresh',
        'status',
        'stats',
        'error_message',
        'started_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'regions_size_bytes'=> 'integer',
            'cities_size_bytes' => 'integer',
            'fresh'             => 'boolean',
            'status'            => LocationImportStatus::class,
            'stats'             => 'array',
            'started_at'        => 'datetime',
            'completed_at'      => 'datetime',
        ];
    }
}
