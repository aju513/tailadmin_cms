<?php

namespace App\Http\Requests\Admin\ResourceCategory;

class UpdateResourceCategoryRequest extends SaveResourceCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.edit') ?? false;
    }
}
