<?php

namespace App\Http\Requests\NoticeCategory;

use Illuminate\Foundation\Http\FormRequest;

class CreateNoticeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
