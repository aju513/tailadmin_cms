<?php

namespace App\Http\Requests\Admin\Content;

class UpdateTagRequest extends StoreTagRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tags.edit') ?? false;
    }
}
