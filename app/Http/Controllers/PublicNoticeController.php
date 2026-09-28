<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use App\Services\SiteSettingService;
use Illuminate\View\View;

class PublicNoticeController extends Controller
{
    public function __construct(private readonly NoticeRepositoryInterface $notices, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings) {}

    private function shell(): array
    {
        return ['settings' => $this->settings->all(), 'mainMenu' => $this->menus->forLocation('header'), 'footerMenu' => $this->menus->forLocation('footer')];
    }

    public function index(): View
    {
        return view('public.notices.index', [...$this->shell(), 'items' => $this->notices->paginatePublished(), 'title' => 'Notices']);
    }

    public function show(string $slug): View
    {
        $item = $this->notices->publishedBySlug($slug);

        return view('public.notices.show', [...$this->shell(), 'item' => $item, 'page' => $item, 'title' => $item->meta_title ?: $item->title]);
    }
}
