<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Services\SiteSettingService;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __construct(private readonly HomepageSlideRepositoryInterface $slides, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings, private readonly PageRepositoryInterface $pages, private readonly NewsRepositoryInterface $news) {}

    public function __invoke(): View
    {
        return view('public.home', ['slides' => $this->slides->active(), 'settings' => $this->settings->all(), 'mainMenu' => $this->menus->forLocation('header'), 'footerMenu' => $this->menus->forLocation('footer'), 'featuredPages' => $this->pages->paginateForIndex(['status' => 'published']), 'latestNews' => $this->news->latestPublished()]);
    }
}
