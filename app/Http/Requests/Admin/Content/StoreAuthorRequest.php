<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('authors.create') ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255'], 'bio' => ['nullable', 'string', 'max:4000'], 'email' => ['nullable', 'email', 'max:255'], 'status' => ['nullable', 'boolean']];
    }
}
