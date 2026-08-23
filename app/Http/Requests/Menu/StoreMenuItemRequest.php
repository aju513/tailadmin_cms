<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') ?? false;
    }

    public function rules(): array
    {
        return ['menu_id' => ['required', 'exists:menus,id'], 'parent_id' => ['nullable', 'exists:menu_items,id'], 'label' => ['required', 'string', 'max:255'], 'page_id' => ['nullable', 'exists:pages,id'], 'external_url' => ['nullable', 'url', 'max:1000'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_visible' => ['nullable', 'boolean']];
    }
}
