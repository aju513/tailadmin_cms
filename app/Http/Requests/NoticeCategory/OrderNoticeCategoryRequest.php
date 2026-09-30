<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;

class OrderNoticeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'integer', 'distinct', 'exists:notice_categories,id'],
        ];
    }
}
