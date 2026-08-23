<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAuthor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'bio', 'email', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
