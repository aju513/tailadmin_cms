<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grievance extends Model
{
    use HasFactory;

    protected $fillable = ['reference', 'page_id', 'page_path', 'page_title', 'full_name', 'email', 'phone', 'subject', 'message', 'attachment_path', 'attachment_name', 'attachment_mime', 'attachment_size'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
