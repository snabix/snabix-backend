<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Models;

use App\Media\Infrastructure\Models\EloquentMedia;
use App\News\Domain\Enums\NewsPostBlockType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property      string               $id
 * @property      string               $news_post_id
 * @property      NewsPostBlockType    $type
 * @property      int                  $sort_order
 * @property      array<string, mixed> $data
 * @property-read EloquentNewsPost     $post
 * @property-read EloquentMedia|null   $blockMedia
 */
class EloquentNewsPostBlock extends Model implements HasMedia
{
    use HasUuids;
    use InteractsWithMedia;

    public const string MEDIA_COLLECTION = 'news_block_media';

    public $incrementing                 = false;

    protected $table                     = 'news_post_blocks';

    protected $keyType                   = 'string';

    /** @var list<string> */
    protected $fillable                  = [
        'id',
        'news_post_id',
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
     * @return MorphOne<EloquentMedia, $this>
     */
    public function blockMedia(): MorphOne
    {
        return $this
            ->morphOne(EloquentMedia::class, 'model')
            ->where('collection_name', self::MEDIA_COLLECTION)
            ->latestOfMany();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION)
            ->useDisk('public')
            ->singleFile();
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
