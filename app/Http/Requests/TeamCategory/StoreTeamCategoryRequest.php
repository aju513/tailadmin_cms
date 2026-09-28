<?php

namespace App\Http\Requests\TeamCategory;

class StoreTeamCategoryRequest extends SaveTeamCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-categories.create') ?? false;
    }
}
