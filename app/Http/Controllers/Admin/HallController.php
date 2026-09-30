<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hall\CreateHallRequest;
use App\Http\Requests\Hall\DeleteHallRequest;
use App\Http\Requests\Hall\EditHallRequest;
use App\Http\Requests\Hall\IndexHallRequest;
use App\Http\Requests\Hall\ShowHallRequest;
use App\Http\Requests\Hall\StoreHallRequest;
use App\Http\Requests\Hall\UpdateHallRequest;
use App\Models\Hall;
use App\Services\HallService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HallController extends Controller
{
    public function __construct(private readonly HallService $service) {}

    public function index(IndexHallRequest $request): View
    {
        return view('pages.admin.halls.index', ['items' => $this->service->index($request->validated()), 'title' => 'Halls']);
    }

    public function create(CreateHallRequest $request): View
    {
        return view('pages.admin.halls.create', ['hall' => $this->service->newHall(), 'title' => 'Add Hall']);
    }

    public function store(StoreHallRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('halls.manage') ? 'admin.halls.index' : 'admin.halls.create')->with('success', 'Hall created.');
    }

    public function show(ShowHallRequest $request, Hall $hall): View
    {
        return view('pages.admin.halls.show', ['hall' => $this->service->details($hall), 'title' => 'Hall Details']);
    }

    public function edit(EditHallRequest $request, Hall $hall): View
    {
        return view('pages.admin.halls.edit', ['hall' => $this->service->details($hall), 'title' => 'Edit Hall']);
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
}
