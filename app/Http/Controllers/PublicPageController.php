<?php

namespace App\Http\Controllers;

use App\Http\Requests\Page\ShowPublicPageRequest;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Services\SiteSettingService;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function __construct(private readonly PageRepositoryInterface $pages, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings, private readonly \App\Services\ResourceDocumentService $resources, private readonly \App\Services\NoticeService $notices) {}

    public function show(ShowPublicPageRequest $request, string $path): View
    {
        $locale = config('settings.nepali') ? ($request->validated('lang') ?? $request->session()->get('page_locale', 'en')) : 'en';
        app()->setLocale($locale);
        if (config('settings.nepali')) {
            $request->session()->put('page_locale', $locale);
        }
        $page = $this->pages->publicByPath($path);

        return view('public.page', ['page' => $page, 'resources' => $this->resources->forPage($page), 'notices' => $this->notices->forPage($page), 'settings' => $this->settings->all(), 'mainMenu' => $this->menus->forLocation('header'), 'footerMenu' => $this->menus->forLocation('footer'), 'title' => $page->meta_title ?: $page->title]);
    }
}
