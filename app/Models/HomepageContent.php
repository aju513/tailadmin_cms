<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class HomepageContent extends Model
{
    use HasFactory, HasTranslations;

    public const KEY = 'home';

    public array $translatable = ['title', 'subtitle', 'body'];

    protected $fillable = ['key', 'title', 'subtitle', 'body', 'meta_title', 'meta_keywords', 'meta_description', 'social_media_id', 'created_by', 'updated_by'];

    public function galleryImages(): HasMany
    {
        return $this->hasMany(HomepageGalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'social_media_id');
    }
}
