<?php

namespace App\Http\Requests\TeamMember;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-members.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
