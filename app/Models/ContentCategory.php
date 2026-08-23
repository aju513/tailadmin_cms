<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'status', 'sort_order', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
