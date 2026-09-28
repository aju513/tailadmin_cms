<?php

namespace App\Http\Requests\TeamCategory;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTeamCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-categories.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
