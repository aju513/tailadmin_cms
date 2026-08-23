<?php

namespace App\Http\Requests\Content;

class IndexCategoryRequest extends IndexContentRequest
{
    protected function ability(): string
    {
        return 'categories.manage';
    }
}
