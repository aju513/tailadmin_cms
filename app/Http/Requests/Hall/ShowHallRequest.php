<?php

namespace App\Http\Requests\Hall;

use Illuminate\Foundation\Http\FormRequest;

class ShowHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.show') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
