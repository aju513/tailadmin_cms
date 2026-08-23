<?php

namespace App\Http\Requests\Page;

use Illuminate\Foundation\Http\FormRequest;

class DeletePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
