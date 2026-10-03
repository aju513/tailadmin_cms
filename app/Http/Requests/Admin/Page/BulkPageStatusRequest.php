<?php

namespace App\Http\Requests\Admin\Page;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkPageStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pages.publish') ?? false;
    }

    public function rules(): array
    {
        return [
            'pages' => ['required', 'array', 'min:1'],
            'pages.*' => ['integer', 'distinct', Rule::exists('pages', 'id')],
            'status' => ['required', Rule::enum(ContentStatus::class)],
        ];
    }
}
