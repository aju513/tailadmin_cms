<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapacityReport extends Model
{
    use HasFactory;

    protected $fillable = ['fiscal_year', 'development', 'collaboration', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['development' => 'array', 'collaboration' => 'array'];
    }
}
