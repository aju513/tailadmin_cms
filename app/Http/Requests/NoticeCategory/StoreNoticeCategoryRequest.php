<?php

namespace App\Http\Requests\NoticeCategory;

class StoreNoticeCategoryRequest extends SaveNoticeCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notice-categories.create') ?? false;
    }
}
