<?php

namespace App\Http\Requests\Admin\TeamCategory;

class UpdateTeamCategoryRequest extends SaveTeamCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-categories.edit') ?? false;
    }
}
