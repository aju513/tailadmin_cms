<?php

namespace App\Http\Requests\TeamMember;

class UpdateTeamMemberRequest extends StoreTeamMemberRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-members.edit') ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['photo'] = ['nullable', 'image', 'max:5120'];

        return $rules;
    }
}
