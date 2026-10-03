<?php

namespace App\Http\Requests\Admin\Hall;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'halls' => ['required', 'array', 'min:1'],
            'halls.*' => ['required', 'integer', 'distinct', Rule::exists('halls', 'id')],

        ];
    }
}
