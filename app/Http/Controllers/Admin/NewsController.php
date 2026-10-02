<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\News\BulkDeleteNewsRequest;
use App\Http\Requests\News\BulkNewsStatusRequest;
use App\Http\Requests\News\DeleteNewsRequest;
use App\Http\Requests\News\IndexNewsRequest;
use App\Http\Requests\News\OrderNewsRequest;
use App\Http\Requests\News\PublishNewsRequest;
use App\Http\Requests\News\ShowNewsRequest;
use App\Http\Requests\News\StoreNewsRequest;
use App\Http\Requests\News\UpdateNewsRequest;
use App\Models\News;
use App\Repositories\Contracts\NewsRepositoryInterface;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function __construct(private readonly NewsRepositoryInterface $news, private readonly NewsService $service) {}

    public function index(IndexNewsRequest $request): View
    {
        return view('pages.admin.news.index', ['items' => $this->news->paginateAdmin($request->validated()), 'title' => 'News']);
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
        return view('pages.admin.news.show', ['item' => $news->load(['thumbnailMedia', 'bannerMedia', 'socialMedia']), 'title' => 'News Details']);
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

    public function publish(PublishNewsRequest $request, News $news): RedirectResponse
    {
        $this->service->publish($news, $request->user());

        return back()->with('success', 'News published.');
    }

    public function unpublish(PublishNewsRequest $request, News $news): RedirectResponse
    {
        $this->service->unpublish($news, $request->user());

        return back()->with('success', 'News unpublished.');
    }

    public function destroy(DeleteNewsRequest $request, News $news): RedirectResponse
    {
        $this->service->delete($news, $request->user());

        return redirect()->route('admin.news.index')->with('success', 'News deleted.');
    }

    public function bulkStatus(BulkNewsStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus($request->validated('news'), ContentStatus::from($request->validated('status')), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => (string) $request->validated('status')], $request->validated('news')),
            ]);
        }

        return back()->with('success', 'Selected news status updated.');
    }

    public function bulkDestroy(BulkDeleteNewsRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('news'), $request->user());

        return back()->with('success', 'Selected news deleted.');
    }

    public function order(OrderNewsRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->reorder($request->validated('news'), $request->user());

        return response()->json(['message' => 'News order updated.']);
    }

    private function formView(string $view, News $news, string $title): View
    {
        return view($view, ['item' => $news, 'title' => $title]);
    }
}
