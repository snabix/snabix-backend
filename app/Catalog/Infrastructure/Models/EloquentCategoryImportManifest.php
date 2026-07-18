<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Models;

use App\Catalog\Domain\Enums\CategoryImportStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string                           $id
 * @property string                           $source
 * @property string                           $source_version
 * @property string|null                      $source_url
 * @property string                           $checksum
 * @property CategoryImportStatus             $status
 * @property array<int, array<string, mixed>> $records
 * @property array<int, array<string, mixed>> $diff
 * @property array<string, int>               $stats
 * @property \Carbon\CarbonImmutable|null     $applied_at
 * @property \Carbon\CarbonImmutable|null     $rolled_back_at
 */
class EloquentCategoryImportManifest extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table     = 'category_import_manifests';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'source',
        'source_version',
        'source_url',
        'checksum',
        'status',
        'records',
        'diff',
        'stats',
        'applied_at',
        'rolled_back_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'         => CategoryImportStatus::class,
            'records'        => 'array',
            'diff'           => 'array',
            'stats'          => 'array',
            'applied_at'     => 'immutable_datetime',
            'rolled_back_at' => 'immutable_datetime',
        ];
    }
}
