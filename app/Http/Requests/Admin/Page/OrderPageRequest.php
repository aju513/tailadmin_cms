<?php

namespace App\Http\Requests\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;

class OrderPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.edit') ?? false;
    }

    public function rules(): array
    {
        return ['pages' => ['required', 'array', 'min:1'], 'pages.*' => ['required', 'integer', 'distinct', 'exists:pages,id']];
    }
}
