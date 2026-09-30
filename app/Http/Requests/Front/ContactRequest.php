<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'mail' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:100'],
            'country' => ['required', Rule::in(['NEP', 'US', 'CA', 'FR', 'DE'])],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
