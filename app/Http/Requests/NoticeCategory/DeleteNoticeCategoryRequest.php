<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;

class DeleteNoticeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
