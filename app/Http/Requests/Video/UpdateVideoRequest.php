<?php

namespace App\Http\Requests\Video;

class UpdateVideoRequest extends SaveVideoRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.edit') ?? false;
    }
}
