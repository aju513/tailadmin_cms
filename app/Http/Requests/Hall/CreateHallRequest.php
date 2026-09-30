<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class CreateHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
