<?php

namespace App\Http\Requests\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class EditResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
