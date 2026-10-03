<?php

namespace App\Http\Requests\Admin\ResourceDocument;

class UpdateResourceDocumentRequest extends SaveResourceDocumentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.edit') ?? false;
    }
}
