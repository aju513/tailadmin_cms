<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Page\BulkDeletePageRequest;
use App\Http\Requests\Admin\Page\BulkPageStatusRequest;
use App\Http\Requests\Admin\Page\DeletePageRequest;
use App\Http\Requests\Admin\Page\IndexPageRequest;
use App\Http\Requests\Admin\Page\OrderPageRequest;
use App\Http\Requests\Admin\Page\PublishPageRequest;
use App\Http\Requests\Admin\Page\ShowPageRequest;
use App\Http\Requests\Admin\Page\StorePageRequest;
use App\Http\Requests\Admin\Page\UpdatePageRequest;
use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageRepositoryInterface $pages, private readonly PageService $service) {}

    public function index(IndexPageRequest $request): View
    {
        return view('admin.pages.pages.index', ['pages' => $this->pages->orderedForIndex($request->validated()), 'pageTypes' => PageType::cases(), 'title' => 'Pages']);
    }

    public function create(): View
    {
        return view('admin.pages.pages.create', ['page' => new Page(['status' => ContentStatus::Draft, 'page_type' => PageType::Article]), 'pageTypes' => PageType::cases(), 'parents' => $this->pages->allForParentSelect(), 'title' => 'Create Page']);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function show(ShowPageRequest $request, Page $page): View
    {
        return view('admin.pages.pages.show', ['page' => $page->load('children', 'bannerMedia', 'socialMedia', 'parent'), 'title' => 'Page Details']);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.pages.edit', ['page' => $page->load('bannerMedia', 'socialMedia'), 'pageTypes' => PageType::cases(), 'parents' => $this->pages->allForParentSelect($page), 'title' => 'Edit Page']);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->service->update($page, $request->validated(), $request->user());

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function publish(PublishPageRequest $request, Page $page): RedirectResponse|JsonResponse
    {
        $page = $this->service->publish($page, $request->user());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Page published.', 'pages' => [['id' => $page->id, 'status' => $page->status->value]]]);
        }

        return back()->with('success', 'Page published.');
    }

    public function unpublish(PublishPageRequest $request, Page $page): RedirectResponse|JsonResponse
    {
        $page = $this->service->unpublish($page, $request->user());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Page unpublished.', 'pages' => [['id' => $page->id, 'status' => $page->status->value]]]);
        }

        return back()->with('success', 'Page unpublished.');
    }

    public function destroy(DeletePageRequest $request, Page $page): RedirectResponse
    {
        $this->service->delete($page, $request->user());

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    public function bulkStatus(BulkPageStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus($request->validated('pages'), ContentStatus::from($request->validated('status')), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected page statuses updated.',
                'pages' => array_map(fn ($id) => ['id' => (int) $id, 'status' => $request->validated('status')], $request->validated('pages')),
            ]);
        }

        return back()->with('success', 'Selected page statuses updated.');
    }

    public function bulkDestroy(BulkDeletePageRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('pages'), $request->user());

        return back()->with('success', 'Selected pages deleted.');
    }

    public function order(OrderPageRequest $request): RedirectResponse
    {
        $this->service->reorder($request->validated('pages'), $request->user());

        return back()->with('success', 'Page order updated.');
    }
}
