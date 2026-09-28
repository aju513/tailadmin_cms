<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menu\AssignMenuPagesRequest;
use App\Http\Requests\Menu\DeleteMenuItemRequest;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(private readonly MenuService $menus) {}

    public function index(?string $location = null): View
    {
        $menus = $this->menus->locations();

        if ($location !== null) {
            $menus = $menus->where('location', $location)->values();
        }

        return view('pages.admin.menus.index', [
            'headerMenus' => $menus->where('location', 'header')->values(),
            'footerMenus' => $menus->where('location', 'footer')->values(),
            'dynamicMenus' => $menus->whereNotIn('location', ['header', 'footer'])->values(),
            'availablePages' => $menus->mapWithKeys(fn ($menu) => [$menu->id => $this->menus->availablePages($menu)]),
            'menuLocation' => $location,
            'title' => 'Public Menus',
        ]);
    }

    public function header(): View
    {
        return $this->index('header');
    }

    public function footer(): View
    {
        return $this->index('footer');
    }

    public function assign(AssignMenuPagesRequest $request): RedirectResponse
    {
        $menu = $this->menus->assignPages($request->validated(), $request->user());

        return redirect()->route($menu->location === 'header' ? 'admin.menus.header' : ($menu->location === 'footer' ? 'admin.menus.footer' : 'admin.menus.index'))->with('success', 'Pages assigned to menu.');
    }

    public function destroy(DeleteMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $this->menus->delete($menuItem, $request->user());

        return back()->with('success', 'Menu item deleted.');
    }
}
