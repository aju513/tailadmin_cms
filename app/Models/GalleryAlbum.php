<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'cover_media_id', 'event_date', 'status', 'sort_order', 'published_at', 'created_by', 'updated_by', 'published_by'];

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime', 'event_date' => 'date'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'cover_media_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

}
