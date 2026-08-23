<?php

namespace App\Http\Requests\Content;

class DeleteAuthorRequest extends DeleteContentRequest
{
    protected function ability(): string
    {
        return 'authors.delete';
    }
}
