<?php

namespace App\Http\Requests\Admin\Hall;

use Illuminate\Foundation\Http\FormRequest;

class DeleteHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
