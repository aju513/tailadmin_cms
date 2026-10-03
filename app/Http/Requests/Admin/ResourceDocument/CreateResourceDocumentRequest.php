<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class CreateResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
