<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;

class CreateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.create') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
