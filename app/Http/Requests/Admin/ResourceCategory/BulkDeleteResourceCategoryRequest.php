<?php

namespace App\Http\Requests\Admin\ResourceCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteResourceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.delete') ?? false;
    }

    public function rules(): array
    {
        return ['categories' => ['required', 'array', 'min:1'], 'categories.*' => ['integer', 'distinct', Rule::exists('resource_categories', 'id')]];
    }
}
