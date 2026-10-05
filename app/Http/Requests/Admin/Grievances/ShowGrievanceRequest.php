<?php

namespace App\Http\Requests\Admin\Grievances;

use Illuminate\Foundation\Http\FormRequest;

class ShowGrievanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('grievances.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
