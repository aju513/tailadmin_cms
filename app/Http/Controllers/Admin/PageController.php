<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Page\BulkDeletePageRequest;
use App\Http\Requests\Page\BulkPageStatusRequest;
use App\Http\Requests\Page\DeletePageRequest;
use App\Http\Requests\Page\IndexPageRequest;
use App\Http\Requests\Page\OrderPageRequest;
use App\Http\Requests\Page\PublishPageRequest;
use App\Http\Requests\Page\ShowPageRequest;
use App\Http\Requests\Page\StorePageRequest;
use App\Http\Requests\Page\UpdatePageRequest;
use App\Models\Page;
use App\Repositories\Contracts\PageRepositoryInterface;
use App\Services\PageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly PageRepositoryInterface $pages, private readonly PageService $service, private readonly \App\Services\ResourceCategoryService $resourceCategories) {}

    public function index(IndexPageRequest $request): View
    {
        return view('pages.admin.pages.index', ['pages' => $this->pages->orderedForIndex($request->validated()), 'pageTypes' => PageType::cases(), 'title' => 'Pages']);
    }

    public function create(): View
    {
        return view('pages.admin.pages.create', ['page' => new Page(['status' => ContentStatus::Draft, 'page_type' => PageType::Article]), 'pageTypes' => PageType::cases(), 'parents' => $this->pages->allForParentSelect(), 'resourceCategories' => $this->resourceCategories->options(), 'title' => 'Create Page']);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $this->service->create($request->validated(), $request->user());

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function show(ShowPageRequest $request, Page $page): View
    {
        return view('pages.admin.pages.show', ['page' => $page->load('children', 'bannerMedia', 'socialMedia', 'parent', 'resourceCategory'), 'title' => 'Page Details']);
    }

    public function edit(Page $page): View
    {
        return view('pages.admin.pages.edit', ['page' => $page->load('bannerMedia', 'socialMedia'), 'pageTypes' => PageType::cases(), 'parents' => $this->pages->allForParentSelect($page), 'resourceCategories' => $this->resourceCategories->options(), 'title' => 'Edit Page']);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->service->update($page, $request->validated(), $request->user());

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function publish(PublishPageRequest $request, Page $page): RedirectResponse
    {
        $this->service->publish($page, $request->user());

        return back()->with('success', 'Page published.');
    }

    public function unpublish(PublishPageRequest $request, Page $page): RedirectResponse
    {
        $this->service->unpublish($page, $request->user());

        return back()->with('success', 'Page unpublished.');
    }

    public function destroy(DeletePageRequest $request, Page $page): RedirectResponse
    {
        $this->service->delete($page, $request->user());

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    public function bulkStatus(BulkPageStatusRequest $request): RedirectResponse
    {
        $this->service->bulkChangeStatus($request->validated('pages'), ContentStatus::from($request->validated('status')), $request->user());

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
