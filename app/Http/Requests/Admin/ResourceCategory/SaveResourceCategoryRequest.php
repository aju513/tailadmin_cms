<?php

namespace App\Http\Requests\Admin\ResourceCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SaveResourceCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('resource_categories', 'slug')->ignore($this->route('resourceCategory')?->id)],
            'description' => ['nullable', 'string', 'max:20000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
