<?php

namespace App\Http\Requests\Content;

class IndexAuthorRequest extends IndexContentRequest
{
    protected function ability(): string
    {
        return 'authors.manage';
    }
}
