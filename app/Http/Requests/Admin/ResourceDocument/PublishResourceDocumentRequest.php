<?php

namespace App\Http\Requests\Admin\ResourceDocument;

use Illuminate\Foundation\Http\FormRequest;

class PublishResourceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('resources.publish') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
