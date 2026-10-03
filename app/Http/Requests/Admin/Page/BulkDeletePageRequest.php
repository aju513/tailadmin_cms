<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeletePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['integer', 'distinct', Rule::exists('pages', 'id')],
        ];
    }
}
