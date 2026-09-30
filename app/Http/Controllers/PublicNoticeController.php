<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Services\NoticeService;
use App\Http\Requests\Notice\IndexPublicNoticeRequest;
use App\Http\Requests\Notice\ShowPublicNoticeRequest;
use App\Services\SiteSettingService;
use Illuminate\View\View;

class PublicNoticeController extends Controller
{
    public function __construct(private readonly NoticeService $notices, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings) {}

    private function shell(): array
    {
        return ['settings' => $this->settings->all(), 'mainMenu' => $this->menus->forLocation('header'), 'footerMenu' => $this->menus->forLocation('footer')];
    }

    public function index(IndexPublicNoticeRequest $request): View
    {
        return view('public.notices.index', [...$this->shell(), 'items' => $this->notices->publicIndex($request->validated()), 'categories' => $this->notices->categoryOptions(true), 'title' => 'Notices']);
    }

    public function show(ShowPublicNoticeRequest $request, string $slug): View
    {
        $item = $this->notices->publicDetails($slug);

        return view('public.notices.show', [...$this->shell(), 'item' => $item, 'page' => $item, 'title' => $item->meta_title ?: $item->title]);
    }
}
