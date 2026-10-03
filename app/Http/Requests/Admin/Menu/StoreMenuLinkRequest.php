<?php

namespace App\Http\Requests\Admin\Menu;

use App\Services\Frontend\SafeHtml;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'label' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $this->integer('menu_id'))],
            'external_url' => ['required', 'string', 'max:1000', function (string $attribute, mixed $value, Closure $fail): void {
                if (! app(SafeHtml::class)->safeUrl($value, true)) {
                    $fail('Enter a website path beginning with / or a valid HTTP(S) link.');
                }
            }],
        ];
    }
}
