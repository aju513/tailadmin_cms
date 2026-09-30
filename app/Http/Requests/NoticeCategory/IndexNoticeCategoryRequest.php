<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;

class IndexNoticeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:255']];
    }
}
