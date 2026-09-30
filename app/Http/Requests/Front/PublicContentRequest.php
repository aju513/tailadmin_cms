<?php

namespace App\Http\Requests\Front;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicContentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('q') && ! $this->has('search')) {
            $this->merge(['search' => $this->input('q')]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'], 'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'resources_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'notices_page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'notice_category_id' => ['nullable', 'integer', 'min:1'],
            'team_category_id' => ['nullable', 'integer', 'min:1'],
            'category' => ['nullable', 'string', 'max:180'],
            'lang' => ['nullable', Rule::in(['en', 'ne'])],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        abort(404);
    }
}
