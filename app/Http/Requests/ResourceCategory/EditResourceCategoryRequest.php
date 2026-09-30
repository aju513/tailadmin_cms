<?php

namespace App\Http\Requests\ResourceCategory;

use Illuminate\Foundation\Http\FormRequest;

class EditResourceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resource-categories.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
