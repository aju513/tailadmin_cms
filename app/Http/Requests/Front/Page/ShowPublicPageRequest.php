<?php

namespace App\Http\Requests\Front\Page;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowPublicPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['notices_page' => ['nullable', 'integer', 'min:1', 'max:100000'], 'resources_page' => ['nullable', 'integer', 'min:1', 'max:100000'], 'lang' => ['nullable', Rule::in(['en', 'ne'])]];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(404);
    }
}
