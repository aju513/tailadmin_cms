<?php

namespace App\Http\Requests\Admin\Content;

use Illuminate\Foundation\Http\FormRequest;

class DeleteContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can($this->ability()) ?? false;
    }

    public function rules(): array
    {
        return [];
    }

    protected function ability(): string
    {
        return 'content.delete';
    }
}
