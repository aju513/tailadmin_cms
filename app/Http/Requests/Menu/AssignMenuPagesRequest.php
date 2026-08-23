<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class AssignMenuPagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'page_ids' => ['required', 'array', 'min:1'],
            'page_ids.*' => ['integer', 'distinct', 'exists:pages,id'],
        ];
    }
}
