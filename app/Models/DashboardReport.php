<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardReport extends Model
{
    protected $fillable = ['cache_key', 'payload', 'expires_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'expires_at' => 'datetime'];
    }
}
