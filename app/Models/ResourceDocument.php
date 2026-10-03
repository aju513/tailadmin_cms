<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceDocument extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'description', 'resource_category_id', 'file_media_id', 'sort_order', 'status', 'published_at', 'created_by', 'updated_by', 'published_by'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return ['status' => ContentStatus::class, 'published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'resource_category_id');
    }

    public function fileMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'file_media_id');
    }
}
