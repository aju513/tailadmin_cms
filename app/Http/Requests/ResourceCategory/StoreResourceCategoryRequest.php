<?php

namespace App\Http\Requests\ResourceCategory;

class StoreResourceCategoryRequest extends SaveResourceCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.create') ?? false;
    }
}
