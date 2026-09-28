<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\News\DeleteNewsRequest;
use App\Http\Requests\News\IndexNewsRequest;
use App\Http\Requests\News\ShowNewsRequest;
use App\Http\Requests\News\StoreNewsRequest;
use App\Http\Requests\News\UpdateNewsRequest;
use App\Models\News;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Services\NewsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(private readonly NewsRepositoryInterface $news, private readonly NewsService $service) {}

    public function index(IndexNewsRequest $request): View
    {
        return view('pages.admin.news.index', ['items' => $this->news->paginateAdmin($request->validated()), 'categories' => $this->news->activeCategories(), 'title' => 'News']);
    }

    public function create(): View
    {
        return $this->formView('pages.admin.news.create', new News(['status' => ContentStatus::Draft, 'featured' => false]), 'Create News');
    }

    public function store(StoreNewsRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.news.index')->with('success', 'News created.');
    }

    public function show(ShowNewsRequest $request, News $news): View
    {
        return view('pages.admin.news.show', ['item' => $news->load(['category', 'author', 'tags', 'thumbnailMedia', 'bannerMedia', 'socialMedia']), 'title' => 'News Details']);
    }

    public function edit(News $news): View
    {
        return $this->formView('pages.admin.news.edit', $news->load(['tags', 'thumbnailMedia', 'bannerMedia', 'socialMedia']), 'Edit News');
    }

    public function update(UpdateNewsRequest $request, News $news): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $news);

        return redirect()->route('admin.news.index')->with('success', 'News updated.');
    }

    public function destroy(DeleteNewsRequest $request, News $news): RedirectResponse
    {
        $this->service->delete($news, $request->user());

        return redirect()->route('admin.news.index')->with('success', 'News deleted.');
    }

    private function formView(string $view, News $news, string $title): View
    {
        return view($view, ['item' => $news, 'categories' => $this->news->activeCategories(), 'authors' => $this->news->activeAuthors(), 'tags' => $this->news->activeTags(), 'title' => $title]);
    }
}
