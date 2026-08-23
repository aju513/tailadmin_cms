<?php

namespace App\Http\Requests\Content;

class DeleteCategoryRequest extends DeleteContentRequest
{
    protected function ability(): string
    {
        return 'categories.delete';
    }
}
