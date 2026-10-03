<?php

namespace App\Http\Requests\Admin\Content;

class IndexCategoryRequest extends IndexContentRequest
{
    protected function ability(): string
    {
        return 'categories.manage';
    }
}
