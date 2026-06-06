<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Models;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Media\Infrastructure\Models\EloquentMedia;
use App\News\Domain\Enums\NewsPostStatus;
use Database\Factories\EloquentNewsPostFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property      string                    $id
 * @property      int|null                  $cover_media_id
 * @property      int|null                  $author_admin_id
 * @property      NewsPostStatus            $status
 * @property      string                    $title
 * @property      string                    $slug
 * @property      string                    $category
 * @property      string|null               $eyebrow
 * @property      string                    $description
 * @property      string|null               $thesis
 * @property      string|null               $reading_time
 * @property      bool                      $is_featured
 * @property      int                       $views_count
 * @property      array<string, mixed>|null $seo
 * @property      Carbon|null               $published_at
 * @property-read EloquentMedia|null        $coverMedia
 * @property-read EloquentAdmin|null        $authorAdmin
 */
class EloquentNewsPost extends Model
{
    /** @use HasFactory<EloquentNewsPostFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $table     = 'news_posts';

    protected $keyType   = 'string';

    /** @var list<string> */
    protected $fillable  = [
        'id',
        'cover_media_id',
        'author_admin_id',
        'status',
        'title',
        'slug',
        'category',
        'eyebrow',
        'description',
        'thesis',
        'reading_time',
        'is_featured',
        'views_count',
        'seo',
        'published_at',
    ];

    protected static function newFactory(): EloquentNewsPostFactory
    {
        return EloquentNewsPostFactory::new();
    }

    /**
     * @return BelongsTo<EloquentMedia, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(EloquentMedia::class, 'cover_media_id');
    }

    /**
     * @return BelongsTo<EloquentAdmin, $this>
     */
    public function authorAdmin(): BelongsTo
    {
        return $this->belongsTo(EloquentAdmin::class, 'author_admin_id');
    }

    /**
     * @return HasMany<EloquentNewsPostBlock, $this>
     */
    public function blocks(): HasMany
    {
        return $this
            ->hasMany(EloquentNewsPostBlock::class, 'news_post_id')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status'       => NewsPostStatus::class,
            'is_featured'  => 'boolean',
            'views_count'  => 'integer',
            'seo'          => 'array',
            'published_at' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }
}
