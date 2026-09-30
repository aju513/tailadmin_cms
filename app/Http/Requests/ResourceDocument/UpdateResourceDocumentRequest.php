<?php

namespace App\Http\Requests\ResourceDocument;

class UpdateResourceDocumentRequest extends SaveResourceDocumentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.edit') ?? false;
    }
}
