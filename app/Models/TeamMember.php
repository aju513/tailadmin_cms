<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'designation', 'category_id', 'bio', 'email', 'phone', 'photo_media_id', 'is_active', 'sort_order', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function photoMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'photo_media_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TeamCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
