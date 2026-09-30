<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Notice\CreateNoticeRequest;
use App\Http\Requests\Notice\EditNoticeRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notice\DeleteNoticeRequest;
use App\Http\Requests\Notice\IndexNoticeRequest;
use App\Http\Requests\Notice\StoreNoticeRequest;
use App\Http\Requests\Notice\UpdateNoticeRequest;
use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function __construct(private readonly NoticeService $service) {}

    public function index(IndexNoticeRequest $request): View
    {
        return view('pages.admin.notices.index', ['items' => $this->service->index($request->validated()), 'types' => $this->service->typeOptions(), 'title' => 'Notices']);
    }

    public function create(CreateNoticeRequest $request): View
    {
        return view('pages.admin.notices.create', ['item' => $this->service->newRecord(), 'types' => $this->service->typeOptions(), 'title' => 'Add Notice']);
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('notices.manage') ? 'admin.notices.index' : 'admin.notices.create')->with('success', 'Notice created.');
    }

    public function edit(EditNoticeRequest $request, Notice $notice): View
    {
        return view('pages.admin.notices.edit', ['item' => $this->service->details($notice), 'types' => $this->service->typeOptions(), 'title' => 'Edit Notice']);
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
}
