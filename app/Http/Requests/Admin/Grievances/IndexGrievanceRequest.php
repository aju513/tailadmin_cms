<?php

namespace App\Http\Requests\Admin\Grievances;

use Illuminate\Foundation\Http\FormRequest;

class IndexGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievances.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']];
    }
}
