<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Services\SiteSettingService;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function __construct(private readonly PageRepositoryInterface $pages, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings) {}

    public function show(string $path): View
    {
        $page = $this->pages->publicByPath($path);

        return view('public.page', ['page' => $page, 'settings' => $this->settings->all(), 'mainMenu' => $this->menus->forLocation('header'), 'footerMenu' => $this->menus->forLocation('footer'), 'title' => $page->meta_title ?: $page->title]);
    }
}
