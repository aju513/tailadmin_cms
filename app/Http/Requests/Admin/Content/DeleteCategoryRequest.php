<?php

namespace App\Http\Requests\Admin\Content;

class DeleteCategoryRequest extends DeleteContentRequest
{
    protected function ability(): string
    {
        return 'categories.delete';
    }
}
