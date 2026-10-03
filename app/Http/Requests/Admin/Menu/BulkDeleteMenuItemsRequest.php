<?php

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'menu_items' => ['required', 'array', 'min:1'],
            'menu_items.*' => ['integer', 'distinct', 'exists:menu_items,id'],
        ];
    }
}
