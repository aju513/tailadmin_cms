<?php

namespace App\Http\Requests\Video;

class StoreVideoRequest extends SaveVideoRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.create') ?? false;
    }
}
