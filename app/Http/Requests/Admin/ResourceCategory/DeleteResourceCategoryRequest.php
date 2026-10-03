<?php

namespace App\Http\Requests\Admin\ResourceCategory;

use Illuminate\Foundation\Http\FormRequest;

class DeleteResourceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
