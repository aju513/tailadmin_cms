<?php

namespace App\Http\Requests\Admin\TeamMember;

use App\Support\UploadProfile;

class UpdateTeamMemberRequest extends StoreTeamMemberRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-members.edit') ?? false;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['photo'] = UploadProfile::rules('images.team_member');

        return $rules;
    }
}
