<?php

namespace App\Http\Requests\Admin\Content;

class IndexAuthorRequest extends IndexContentRequest
{
    protected function ability(): string
    {
        return 'authors.manage';
    }
}
