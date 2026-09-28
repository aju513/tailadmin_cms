<?php

namespace App\Http\Requests\News;

class UpdateNewsRequest extends SaveNewsRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.edit') ?? false;
    }
}
