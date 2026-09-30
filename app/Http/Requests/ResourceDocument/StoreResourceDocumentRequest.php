<?php

namespace App\Http\Requests\ResourceDocument;

class StoreResourceDocumentRequest extends SaveResourceDocumentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.create') ?? false;
    }
}
