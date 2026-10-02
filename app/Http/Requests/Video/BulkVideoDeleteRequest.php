<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkVideoDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.delete') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*' => ['required', 'integer', 'distinct', Rule::exists('videos', 'id')],

        ];
    }
}
