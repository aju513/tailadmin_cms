<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;

class EditNoticeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
