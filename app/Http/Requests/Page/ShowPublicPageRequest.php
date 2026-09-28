<?php

namespace App\Http\Requests\Page;

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
        return ['lang' => ['nullable', Rule::in(['en', 'ne'])]];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(404);
    }
}
