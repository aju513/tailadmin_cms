<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Hall\BulkDeleteHallRequest;
use App\Http\Requests\Admin\Hall\BulkHallStatusRequest;
use App\Http\Requests\Admin\Hall\CreateHallRequest;
use App\Http\Requests\Admin\Hall\DeleteHallRequest;
use App\Http\Requests\Admin\Hall\EditHallRequest;
use App\Http\Requests\Admin\Hall\IndexHallRequest;
use App\Http\Requests\Admin\Hall\ShowHallRequest;
use App\Http\Requests\Admin\Hall\StoreHallRequest;
use App\Http\Requests\Admin\Hall\UpdateHallRequest;
use App\Models\Hall;
use App\Services\HallService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HallController extends Controller
{
    public function __construct(private readonly HallService $service) {}

    public function index(IndexHallRequest $request): View
    {
        return view('admin.pages.halls.index', ['items' => $this->service->index($request->validated()), 'title' => 'Halls']);
    }

    public function create(CreateHallRequest $request): View
    {
        return view('admin.pages.halls.create', ['hall' => $this->service->newHall(), 'title' => 'Add Hall']);
    }

    public function store(StoreHallRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('halls.manage') ? 'admin.halls.index' : 'admin.halls.create')->with('success', 'Hall created.');
    }

    public function show(ShowHallRequest $request, Hall $hall): View
    {
        return view('admin.pages.halls.show', ['hall' => $this->service->details($hall), 'title' => 'Hall Details']);
    }

    public function edit(EditHallRequest $request, Hall $hall): View
    {
        return view('admin.pages.halls.edit', ['hall' => $this->service->details($hall), 'title' => 'Edit Hall']);
    }

    public function update(UpdateHallRequest $request, Hall $hall): RedirectResponse
    {
        $hall = $this->service->save($request->validated(), $request->user(), $hall);

        return redirect()->route('admin.halls.edit', $hall)->with('success', 'Hall updated.');
    }

    public function destroy(DeleteHallRequest $request, Hall $hall): RedirectResponse
    {
        $this->service->delete($hall, $request->user());

        return back()->with('success', 'Hall deleted.');
    }

    public function bulkStatus(BulkHallStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkStatus($request->validated('halls'), \App\Enums\ContentStatus::from($request->validated('status')), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => (string) $request->validated('status')], $request->validated('halls')),
            ]);
        }

        return back()->with('success', 'Selected hall statuses updated.');
    }

    public function bulkDestroy(BulkDeleteHallRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('halls'), $request->user());

        return back()->with('success', 'Selected halls deleted.');
    }
}
