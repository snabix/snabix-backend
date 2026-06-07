<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Models;

use App\Media\Infrastructure\Models\EloquentMedia;
use App\News\Domain\Enums\NewsPostBlockType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property      string               $id
 * @property      string               $news_post_id
 * @property      int|null             $media_id
 * @property      NewsPostBlockType    $type
 * @property      int                  $sort_order
 * @property      array<string, mixed> $data
 * @property-read EloquentNewsPost     $post
 * @property-read EloquentMedia|null   $media
 */
class EloquentNewsPostBlock extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $table     = 'news_post_blocks';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'id',
        'news_post_id',
        'media_id',
        'type',
        'sort_order',
        'data',
    ];

    /**
     * @return BelongsTo<EloquentNewsPost, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(EloquentNewsPost::class, 'news_post_id');
    }

    /**
     * @return BelongsTo<EloquentMedia, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(EloquentMedia::class, 'media_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type'       => NewsPostBlockType::class,
            'sort_order' => 'integer',
            'data'       => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
