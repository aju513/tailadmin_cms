<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageSlide extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'subtitle', 'link_url', 'media_id', 'status', 'sort_order', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['status' => ContentStatus::class];
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_id');
    }
}
