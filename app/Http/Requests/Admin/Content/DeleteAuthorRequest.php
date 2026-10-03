<?php

namespace App\Http\Requests\Admin\Content;

class DeleteAuthorRequest extends DeleteContentRequest
{
    protected function ability(): string
    {
        return 'authors.delete';
    }
}
