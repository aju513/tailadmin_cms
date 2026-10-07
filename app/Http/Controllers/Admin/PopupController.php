<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Popup\CreatePopupRequest;
use App\Http\Requests\Admin\Popup\DeletePopupRequest;
use App\Http\Requests\Admin\Popup\EditPopupRequest;
use App\Http\Requests\Admin\Popup\IndexPopupRequest;
use App\Http\Requests\Admin\Popup\OrderPopupRequest;
use App\Http\Requests\Admin\Popup\SavePopupRequest;
use App\Models\Popup;
use App\Services\PopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PopupController extends Controller
{
    public function __construct(private readonly PopupService $service) {}

    public function index(IndexPopupRequest $request): View
    {
        return view('admin.pages.popups.index', ['popups' => $this->service->index($request->validated()), 'title' => 'Popup Manager']);
    }

    public function create(CreatePopupRequest $request): View
    {
        return view('admin.pages.popups.create', ['popup' => $this->service->newRecord(), 'title' => 'Add Popup']);
    }

    public function edit(EditPopupRequest $request, Popup $popup): View
    {
        return view('admin.pages.popups.edit', ['popup' => $this->service->details($popup), 'title' => 'Edit Popup']);
    }

    public function store(SavePopupRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.popups.index')->with('success', 'Popup created.');
    }

    public function update(SavePopupRequest $request, Popup $popup): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $popup);

        return redirect()->route('admin.popups.index')->with('success', 'Popup updated.');
    }

    public function destroy(DeletePopupRequest $request, Popup $popup): RedirectResponse
    {
        $this->service->delete($popup, $request->user());

        return back()->with('success', 'Popup deleted.');
    }

    public function order(OrderPopupRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('records'), $request->validated('original_order'), $request->user());

        return response()->json(['message' => 'Popup priority updated.']);
    }
}
