<?php

namespace App\Http\Requests\Admin\ResourceCategory;

use Illuminate\Foundation\Http\FormRequest;

class CreateResourceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
