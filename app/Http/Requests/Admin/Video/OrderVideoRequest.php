<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;

class OrderVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'records.*' => ['required', 'integer', 'distinct', 'exists:videos,id'],
            'original_order' => ['required', 'array', 'list', 'min:1', 'max:1000'],
            'original_order.*' => ['required', 'integer', 'distinct', 'exists:videos,id'],
        ];
    }
}
