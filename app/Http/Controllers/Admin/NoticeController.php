<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\DeleteNoticeRequest;
use App\Http\Requests\Notice\IndexNoticeRequest;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;
use App\Models\Notice;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use App\Services\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeRepositoryInterface $notices, private readonly NoticeService $service) {}

    public function index(IndexNoticeRequest $request): View
    {
        return view('pages.admin.notices.index', ['items' => $this->notices->paginateAdmin($request->validated()), 'title' => 'Notices']);
    }

    public function create(): View
    {
        return view('pages.admin.notices.create', ['item' => new Notice(['status' => ContentStatus::Draft, 'sort_order' => 0]), 'title' => 'Create Notice']);
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.notices.index')->with('success', 'Notice created.');
    }

    public function edit(Notice $notice): View
    {
        return view('pages.admin.notices.edit', ['item' => $notice->load('fileMedia'), 'title' => 'Edit Notice']);
    }

    public function update(UpdateNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $notice);

        return redirect()->route('admin.notices.index')->with('success', 'Notice updated.');
    }

    public function destroy(DeleteNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->service->delete($notice, $request->user());

        return back()->with('success', 'Notice deleted.');
    }
}
