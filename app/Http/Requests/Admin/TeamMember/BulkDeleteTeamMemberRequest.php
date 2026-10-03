<?php

namespace App\Http\Requests\Admin\TeamMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-members.delete') ?? false;
    }

    public function rules(): array
    {
        return ['members' => ['required', 'array', 'min:1'], 'members.*' => ['integer', 'distinct', Rule::exists('team_members', 'id')]];
    }
}
