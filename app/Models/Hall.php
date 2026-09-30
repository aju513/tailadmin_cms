<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Hall extends Model
{
    use HasFactory, HasTranslations;

    public array $translatable = ['title', 'summary', 'body', 'booking_instructions'];

    protected $fillable = [
        'title', 'slug', 'building_name', 'location', 'address', 'capacity', 'floor_area',
        'summary', 'body', 'booking_instructions', 'amenities', 'rental_rate', 'rate_unit',
        'contact_person', 'contact_phone', 'contact_email', 'map_url', 'availability_status',
        'status', 'sort_order', 'thumbnail_media_id', 'banner_media_id', 'social_media_id',
        'meta_title', 'meta_description', 'published_at', 'created_by', 'updated_by', 'published_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'capacity' => 'integer',
            'floor_area' => 'decimal:2',
            'rental_rate' => 'decimal:2',
            'amenities' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    public function galleryImages(): HasMany
    {
        return $this->hasMany(HallGalleryImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
