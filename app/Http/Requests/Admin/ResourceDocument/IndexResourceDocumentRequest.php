<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class IndexResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:255']];
    }
}
