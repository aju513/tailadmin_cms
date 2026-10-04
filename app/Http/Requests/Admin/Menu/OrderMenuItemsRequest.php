<?php

namespace App\Http\Requests\Admin\Menu;

use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;

class OrderMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') ?? false;
    }

    public function rules(): array
    {
        $importantLinks = app(MenuRepositoryInterface::class)->location($this->integer('menu_id')) === 'important_links';

        return [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'parent_id' => $importantLinks ? ['prohibited'] : ['nullable', 'integer', 'exists:menu_items,id'],
            'menu_items' => ['required', 'array', 'min:1'],
            'menu_items.*' => ['integer', 'distinct', 'exists:menu_items,id'],
        ];
    }
}
