<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomepageGalleryImage extends Model
{
    protected $fillable = ['homepage_content_id', 'media_asset_id', 'sort_order'];

    public function homepageContent(): BelongsTo
    {
        return $this->belongsTo(HomepageContent::class);
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }
}
