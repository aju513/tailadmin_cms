<?php

namespace App\Http\Requests\Admin\News;

class StoreNewsRequest extends SaveNewsRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('news.create') ?? false;
    }
}
