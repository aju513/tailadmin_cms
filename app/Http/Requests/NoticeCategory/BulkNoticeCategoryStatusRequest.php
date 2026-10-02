<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkNoticeCategoryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.edit') ?? false;
    }

    public function rules(): array
    {
        return ['categories' => ['required', 'array', 'min:1'], 'categories.*' => ['integer', 'distinct', Rule::exists('notice_categories', 'id')], 'status' => ['required', 'boolean']];
    }
}
