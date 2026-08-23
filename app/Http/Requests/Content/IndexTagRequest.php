<?php

namespace App\Http\Requests\Content;

class IndexTagRequest extends IndexContentRequest
{
    protected function ability(): string
    {
        return 'tags.manage';
    }
}
