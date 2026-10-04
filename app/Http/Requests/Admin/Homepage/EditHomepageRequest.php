<?php

namespace App\Http\Requests\Admin\Homepage;

use Illuminate\Foundation\Http\FormRequest;

class EditHomepageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('homepage.manage');
    }

    public function rules(): array
    {
        return [];
    }
}
