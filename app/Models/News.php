<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = ['title', 'slug', 'subtitle', 'summary', 'body', 'category_id', 'author_id', 'thumbnail_media_id', 'banner_media_id', 'social_media_id', 'meta_title', 'meta_keywords', 'meta_description', 'status', 'featured', 'published_at', 'sort_order', 'created_by', 'updated_by', 'published_by'];

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'featured' => 'boolean', 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(ContentAuthor::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContentTag::class, 'news_content_tag');
    }

    public function thumbnailMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'thumbnail_media_id');
    }

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'banner_media_id');
    }

    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'social_media_id');
    }
}
