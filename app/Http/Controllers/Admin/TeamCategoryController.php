<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamCategory\BulkDeleteTeamCategoryRequest;
use App\Http\Requests\TeamCategory\BulkTeamCategoryStatusRequest;
use App\Http\Requests\TeamCategory\DeleteTeamCategoryRequest;
use App\Http\Requests\TeamCategory\IndexTeamCategoryRequest;
use App\Http\Requests\TeamCategory\StoreTeamCategoryRequest;
use App\Http\Requests\TeamCategory\UpdateTeamCategoryRequest;
use App\Models\TeamCategory;
use App\Repositories\Contracts\TeamCategoryRepositoryInterface;
use App\Services\TeamCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TeamCategoryController extends Controller
{
    public function __construct(private readonly TeamCategoryRepositoryInterface $categories, private readonly TeamCategoryService $service) {}

    public function index(IndexTeamCategoryRequest $request): View
    {
        return view('pages.admin.team-categories.index', ['categories' => $this->categories->paginate($request->validated()), 'title' => 'Team Categories']);
    }

    public function create(): View
    {
        return view('pages.admin.team-categories.create', ['category' => new TeamCategory(['status' => true]), 'title' => 'Create Team Category']);
    }

    public function store(StoreTeamCategoryRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.team-categories.index')->with('success', 'Team category created.');
    }

    public function edit(TeamCategory $teamCategory): View
    {
        return view('pages.admin.team-categories.edit', ['category' => $teamCategory, 'title' => 'Edit Team Category']);
    }

    public function update(UpdateTeamCategoryRequest $request, TeamCategory $teamCategory): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $teamCategory);

        return redirect()->route('admin.team-categories.index')->with('success', 'Team category updated.');
    }

    public function destroy(DeleteTeamCategoryRequest $request, TeamCategory $teamCategory): RedirectResponse
    {
        $this->service->delete($teamCategory, $request->user());

        return back()->with('success', 'Team category deleted.');
    }

    public function bulkStatus(BulkTeamCategoryStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkStatus($request->validated('categories'), (bool) $request->validated('status'));
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => ($request->boolean('status') ? '1' : '0')], $request->validated('categories')),
            ]);
        }

        return back()->with('success', 'Selected team category statuses updated.');
    }

    public function bulkDestroy(BulkDeleteTeamCategoryRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('categories'), $request->user());

        return back()->with('success', 'Selected team categories deleted.');
    }
}
