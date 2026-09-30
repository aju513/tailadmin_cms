<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('dashboard.view');
    }

    public function rules(): array
    {
        return ['days' => ['sometimes', 'required', 'integer', Rule::in([7, 30, 90])]];
    }
}
