<?php

namespace App\Http\Controllers;

use App\Http\Requests\News\IndexPublicNewsRequest;
use App\Repositories\Contracts\MenuRepositoryInterface;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Services\SiteSettingService;
use Illuminate\View\View;

class PublicNewsController extends Controller
{
    public function __construct(private readonly NewsRepositoryInterface $news, private readonly MenuRepositoryInterface $menus, private readonly SiteSettingService $settings) {}

    public function index(IndexPublicNewsRequest $request): View
    {
        return $this->listing($request->validated(), 'News');
    }

    public function category(IndexPublicNewsRequest $request, string $slug): View
    {
        $category = $this->news->categoryBySlug($slug);

        return $this->listing([...$request->validated(), 'category' => $slug], $category->name);
    }

    public function tag(IndexPublicNewsRequest $request, string $slug): View
    {
        $tag = $this->news->tagBySlug($slug);

        return $this->listing([...$request->validated(), 'tag' => $slug], $tag->name);
    }

    public function author(IndexPublicNewsRequest $request, string $slug): View
    {
        $author = $this->news->authorBySlug($slug);

        return $this->listing([...$request->validated(), 'author' => $slug], $author->name, $author);
    }

    public function show(string $slug): View
    {
        $item = $this->news->publishedBySlug($slug);

        return view('public.news.show', [...$this->shell(), 'item' => $item, 'page' => $item, 'related' => $this->news->latestPublished($item), 'title' => $item->meta_title ?: $item->title]);
    }

    private function listing(array $filters, string $heading, ?\App\Models\ContentAuthor $author = null): View
    {
        return view('public.news.index', [...$this->shell(), 'items' => $this->news->paginatePublished($filters), 'featured' => $filters === [] ? $this->news->featured() : null, 'categories' => $this->news->activeCategories(), 'heading' => $heading, 'author' => $author, 'title' => $heading]);
    }

    private function shell(): array
    {
        return ['settings' => $this->settings->all(), 'mainMenu' => $this->menus->forLocation('header'), 'footerMenu' => $this->menus->forLocation('footer')];
    }
}
