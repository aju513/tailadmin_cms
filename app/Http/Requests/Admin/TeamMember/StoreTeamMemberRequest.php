<?php

namespace App\Http\Requests\Admin\TeamMember;

use App\Support\UploadProfile;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('team-members.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:team_categories,id'],
            'bio' => ['nullable', 'string', 'max:10000'],
            'photo' => UploadProfile::rules('images.team_member'),
            'photo_alt_text' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
