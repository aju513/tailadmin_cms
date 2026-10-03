<?php

namespace App\Http\Requests\Admin\Video;

class StoreVideoRequest extends SaveVideoRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.create') ?? false;
    }
}
