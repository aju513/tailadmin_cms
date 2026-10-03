<?php

namespace App\Http\Requests\Admin\ResourceDocument;

class StoreResourceDocumentRequest extends SaveResourceDocumentRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.create') ?? false;
    }
}
