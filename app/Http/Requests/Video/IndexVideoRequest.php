<?php

namespace App\Http\Requests\Video;

use Illuminate\Foundation\Http\FormRequest;

class IndexVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:255']];
    }
}
