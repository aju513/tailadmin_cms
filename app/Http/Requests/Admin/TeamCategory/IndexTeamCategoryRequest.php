<?php

namespace App\Http\Requests\Admin\TeamCategory;

use Illuminate\Foundation\Http\FormRequest;

class IndexTeamCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-categories.manage') ?? false;
    }

    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100']];
    }
}
