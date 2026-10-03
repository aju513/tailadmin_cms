<?php

namespace App\Http\Requests\Admin\Hall;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkHallStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('halls.publish') ?? false;
    }

    public function rules(): array
    {
        return [
            'halls' => ['required', 'array', 'min:1'],
            'halls.*' => ['required', 'integer', 'distinct', Rule::exists('halls', 'id')],
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}
