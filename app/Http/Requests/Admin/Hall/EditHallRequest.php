<?php

namespace App\Http\Requests\Admin\Hall;

use Illuminate\Foundation\Http\FormRequest;

class EditHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.edit') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
