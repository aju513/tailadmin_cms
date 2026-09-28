<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'status', 'sort_order', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'category_id');
    }
}
