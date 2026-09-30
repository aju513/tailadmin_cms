<?php

namespace App\Http\Requests\NoticeCategory;

class UpdateNoticeCategoryRequest extends SaveNoticeCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.edit') ?? false;
    }
}
