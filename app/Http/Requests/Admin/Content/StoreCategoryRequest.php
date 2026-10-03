<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('categories.create') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'], 'status' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']];
    }
}
