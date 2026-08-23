<?php

namespace App\Http\Requests\Content;

class DeleteTagRequest extends DeleteContentRequest
{
    protected function ability(): string
    {
        return 'tags.delete';
    }
}
