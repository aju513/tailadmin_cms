<?php

namespace App\Http\Requests\TeamCategory;

class UpdateTeamCategoryRequest extends SaveTeamCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-categories.edit') ?? false;
    }
}
