<?php

namespace App\Http\Requests\Admin\ResourceCategory;

use Illuminate\Foundation\Http\FormRequest;

class OrderResourceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'integer', 'distinct', 'exists:resource_categories,id'],
        ];
    }
}
