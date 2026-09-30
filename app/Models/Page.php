<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\NoticeType;
use App\Enums\PageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['title', 'summary', 'body'];

    protected $fillable = ['notice_category_id', 'notice_type', 'parent_id', 'resource_category_id', 'title', 'page_type', 'slug', 'path', 'summary', 'body', 'status', 'published_at', 'meta_title', 'meta_description', 'banner_media_id', 'social_media_id', 'sort_order', 'created_by', 'updated_by', 'published_by'];

    protected function casts(): array
    {
        return ['notice_type' => NoticeType::class, 'page_type' => PageType::class, 'status' => ContentStatus::class, 'published_at' => 'datetime'];
    }

    public function noticeCategory(): BelongsTo
    {
        return $this->belongsTo(NoticeCategory::class, 'notice_category_id');
    }

    public function resourceCategory(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'banner_media_id');
    }

    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'social_media_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
