<?php

namespace App\Http\Requests\Admin\Menu;

use App\Repositories\Contracts\MenuRepositoryInterface;
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
        $importantLinks = app(MenuRepositoryInterface::class)->location($this->integer('menu_id')) === 'important_links';

        return [
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
            'label' => ['required', 'string', 'max:255'],
            'parent_id' => $importantLinks ? ['prohibited'] : ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $this->integer('menu_id'))],
            'page_id' => ['prohibited'],
            'page_ids' => ['prohibited'],
            'external_url' => ['bail', 'required', 'string', 'max:1000', function (string $attribute, mixed $value, Closure $fail) use ($importantLinks): void {
                $html = app(SafeHtml::class);
                if ($importantLinks ? ! $html->externalUrl($value) : ! $html->safeUrl($value, true)) {
                    $fail($importantLinks ? 'Enter a full HTTP or HTTPS URL for an external link.' : 'Enter a website path beginning with / or a valid HTTP(S) link.');
                }
            }],
        ];
    }
}
