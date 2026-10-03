<?php

namespace App\Http\Requests\Admin\Video;

use Illuminate\Foundation\Http\FormRequest;

class DeleteVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('videos.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
