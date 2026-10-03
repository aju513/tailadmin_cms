<?php

namespace App\Http\Requests\Admin\Content;

class UpdateAuthorRequest extends StoreAuthorRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('authors.edit') ?? false;
    }
}
