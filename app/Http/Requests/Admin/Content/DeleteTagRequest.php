<?php

namespace App\Http\Requests\Admin\Content;

class DeleteTagRequest extends DeleteContentRequest
{
    protected function ability(): string
    {
        return 'tags.delete';
    }
}
