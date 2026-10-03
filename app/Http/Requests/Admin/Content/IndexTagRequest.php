<?php

namespace App\Http\Requests\Admin\Content;

class IndexTagRequest extends IndexContentRequest
{
    protected function ability(): string
    {
        return 'tags.manage';
    }
}
