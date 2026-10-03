<?php

namespace App\Http\Requests\Admin\Content;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.edit') ?? false;
    }
}
