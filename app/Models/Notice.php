<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'file_media_id', 'meta_title', 'meta_description', 'status', 'sort_order', 'published_at', 'created_by', 'updated_by', 'published_by'];

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function fileMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'file_media_id');
    }
}
