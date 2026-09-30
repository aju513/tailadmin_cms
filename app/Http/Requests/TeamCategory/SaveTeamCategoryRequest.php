<?php

namespace App\Http\Requests\TeamCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SaveTeamCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        $u = Rule::unique('team_categories', 'slug');
        if ($this->route('team_category')) {
            $u->ignore($this->route('team_category')->id);
        }

        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'alpha_dash:ascii', $u], 'description' => ['nullable', 'string', 'max:10000'], 'status' => ['nullable', 'boolean'], 'sort_order' => ['nullable', 'integer', 'min:0']];
    }
}
