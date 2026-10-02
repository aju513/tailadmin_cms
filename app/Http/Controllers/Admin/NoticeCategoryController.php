<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NoticeCategory\BulkDeleteNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\BulkNoticeCategoryStatusRequest;
use App\Http\Requests\NoticeCategory\CreateNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\DeleteNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\EditNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\IndexNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\OrderNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\StoreNoticeCategoryRequest;
use App\Http\Requests\NoticeCategory\UpdateNoticeCategoryRequest;
use App\Models\NoticeCategory;
use App\Services\NoticeCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeCategoryController extends Controller
{
    public function __construct(private readonly NoticeCategoryService $service) {}

    public function index(IndexNoticeCategoryRequest $request): View
    {
        return view('pages.admin.notice-categories.index', ['records' => $this->service->index($request->validated()), 'title' => 'Notice Categories']);
    }

    public function create(CreateNoticeCategoryRequest $request): View
    {
        return view('pages.admin.notice-categories.create', ['record' => $this->service->newRecord(), 'title' => 'Add Notice Category']);
    }

    public function store(StoreNoticeCategoryRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('notice-categories.manage') ? 'admin.notice-categories.index' : 'admin.notice-categories.create')
            ->with('success', 'Notice category created.');
    }

    public function edit(EditNoticeCategoryRequest $request, NoticeCategory $noticeCategory): View
    {
        return view('pages.admin.notice-categories.edit', ['record' => $this->service->details($noticeCategory), 'title' => 'Edit Notice Category']);
    }

    public function update(UpdateNoticeCategoryRequest $request, NoticeCategory $noticeCategory): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $noticeCategory);

        return redirect()->route('admin.notice-categories.edit', $record)->with('success', 'Notice category updated.');
    }

    public function order(OrderNoticeCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('categories'), $request->user());

        return response()->json(['message' => 'Category order updated.']);
    }

    public function bulkStatus(BulkNoticeCategoryStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus($request->validated('categories'), (bool) $request->validated('status'), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => ($request->boolean('status') ? '1' : '0')], $request->validated('categories')),
            ]);
        }

        return back()->with('success', 'Selected notice category statuses updated.');
    }

    public function bulkDestroy(BulkDeleteNoticeCategoryRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('categories'), $request->user());

        return back()->with('success', 'Selected notice categories deleted.');
    }

    public function destroy(DeleteNoticeCategoryRequest $request, NoticeCategory $noticeCategory): RedirectResponse
    {
        $this->service->delete($noticeCategory, $request->user());

        return back()->with('success', 'Notice category deleted.');
    }
}
