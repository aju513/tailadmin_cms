<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\BulkDeleteNoticeRequest;
use App\Http\Requests\Notice\BulkNoticeStatusRequest;
use App\Http\Requests\Notice\CreateNoticeRequest;
use App\Http\Requests\Notice\DeleteNoticeRequest;
use App\Http\Requests\Notice\EditNoticeRequest;
use App\Http\Requests\Notice\IndexNoticeRequest;
use App\Http\Requests\Notice\OrderNoticeRequest;
use App\Http\Requests\Notice\PublishNoticeRequest;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;
use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeService $service) {}

    public function index(IndexNoticeRequest $request): View
    {
        return view('pages.admin.notices.index', ['items' => $this->service->index($request->validated()), 'categories' => $this->service->categoryOptions(), 'title' => 'Notices']);
    }

    public function create(CreateNoticeRequest $request): View
    {
        return view('pages.admin.notices.create', ['item' => $this->service->newRecord(), 'categories' => $this->service->categoryOptions(), 'title' => 'Add Notice']);
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('notices.manage') ? 'admin.notices.index' : 'admin.notices.create')->with('success', 'Notice created.');
    }

    public function edit(EditNoticeRequest $request, Notice $notice): View
    {
        return view('pages.admin.notices.edit', ['item' => $this->service->details($notice), 'categories' => $this->service->categoryOptions(), 'title' => 'Edit Notice']);
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $saved = $this->service->save($request->validated(), $request->user(), $notice);

        return redirect()->route('admin.notices.edit', $saved)->with('success', 'Notice updated.');
    }

    public function destroy(DeleteNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->service->delete($notice, $request->user());

        return back()->with('success', 'Notice deleted.');
    }

    public function publish(PublishNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->service->publish($notice, $request->user());

        return back()->with('success', 'Notice published.');
    }

    public function unpublish(PublishNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->service->unpublish($notice, $request->user());

        return back()->with('success', 'Notice unpublished.');
    }

    public function bulkStatus(BulkNoticeStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus($request->validated('notices'), \App\Enums\ContentStatus::from($request->validated('status')), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => (string) $request->validated('status')], $request->validated('notices')),
            ]);
        }

        return back()->with('success', 'Selected notice statuses updated.');
    }

    public function bulkDestroy(BulkDeleteNoticeRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('notices'), $request->user());

        return back()->with('success', 'Selected notices deleted.');
    }

    public function order(OrderNoticeRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->reorder($request->validated('notices'));

        return response()->json(['message' => 'Notice order updated.']);
    }
}
