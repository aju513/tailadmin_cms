<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class IndexContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->ability()) ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100']];
    }

    protected function ability(): string
    {
        return 'content.manage';
    }
}
