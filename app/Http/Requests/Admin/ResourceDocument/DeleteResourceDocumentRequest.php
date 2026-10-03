<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class DeleteResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
