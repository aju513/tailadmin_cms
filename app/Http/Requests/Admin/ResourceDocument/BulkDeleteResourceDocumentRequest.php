<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.delete') ?? false;
    }

    public function rules(): array
    {
        return ['resources' => ['required', 'array', 'min:1'], 'resources.*' => ['integer', 'distinct', Rule::exists('resource_documents', 'id')]];
    }
}
