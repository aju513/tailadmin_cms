<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SaveNoticeCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('notice_categories', 'slug')->ignore($this->route('noticeCategory')?->id)],
            'description' => ['nullable', 'string', 'max:20000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
