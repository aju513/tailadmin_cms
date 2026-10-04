<?php

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMenuPagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'integer', Rule::exists('menus', 'id')->whereNot('location', 'important_links')],
            'page_ids' => ['required', 'array', 'min:1'],
            'page_ids.*' => ['integer', 'distinct', 'exists:pages,id'],
        ];
    }

    public function messages(): array
    {
        return ['menu_id.exists' => 'Choose a menu that supports page assignments. Important Links accepts external links only.'];
    }
}
