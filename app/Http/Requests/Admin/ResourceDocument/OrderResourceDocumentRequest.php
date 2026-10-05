<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class OrderResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'resources' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'resources.*' => ['required', 'integer', 'distinct', 'exists:resource_documents,id'],
            'original_order' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'original_order.*' => ['required', 'integer', 'distinct', 'exists:resource_documents,id'],
        ];
    }
}
