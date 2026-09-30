<?php

namespace App\Http\Requests\Hall;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexHallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'availability_status' => ['nullable', Rule::in(array_keys(config('halls.availability')))],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
