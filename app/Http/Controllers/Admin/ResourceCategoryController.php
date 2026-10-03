<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResourceCategory\BulkDeleteResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\BulkResourceCategoryStatusRequest;
use App\Http\Requests\Admin\ResourceCategory\CreateResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\DeleteResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\EditResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\IndexResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\OrderResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\StoreResourceCategoryRequest;
use App\Http\Requests\Admin\ResourceCategory\UpdateResourceCategoryRequest;
use App\Models\ResourceCategory;
use App\Services\ResourceCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResourceCategoryController extends Controller
{
    public function __construct(private readonly ResourceCategoryService $service) {}

    public function index(IndexResourceCategoryRequest $request): View
    {
        return view('admin.pages.resource-categories.index', ['records' => $this->service->index($request->validated()), 'title' => 'Resource Categories']);
    }

    public function create(CreateResourceCategoryRequest $request): View
    {
        return view('admin.pages.resource-categories.create', ['record' => $this->service->newRecord(), 'title' => 'Add Resource Category']);
    }

    public function store(StoreResourceCategoryRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('resource-categories.manage') ? 'admin.resource-categories.index' : 'admin.resource-categories.create')
            ->with('success', 'Resource category created.');
    }

    public function edit(EditResourceCategoryRequest $request, ResourceCategory $resourceCategory): View
    {
        return view('admin.pages.resource-categories.edit', ['record' => $this->service->details($resourceCategory), 'title' => 'Edit Resource Category']);
    }

    public function update(UpdateResourceCategoryRequest $request, ResourceCategory $resourceCategory): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $resourceCategory);

        return redirect()->route('admin.resource-categories.edit', $record)->with('success', 'Resource category updated.');
    }

    public function order(OrderResourceCategoryRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('categories'), $request->user());

        return response()->json(['message' => 'Category order updated.']);
    }

    public function destroy(DeleteResourceCategoryRequest $request, ResourceCategory $resourceCategory): RedirectResponse
    {
        $this->service->delete($resourceCategory, $request->user());

        return back()->with('success', 'Resource category deleted.');
    }

    public function bulkStatus(BulkResourceCategoryStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus($request->validated('categories'), (bool) $request->validated('status'), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => ($request->boolean('status') ? '1' : '0')], $request->validated('categories')),
            ]);
        }

        return back()->with('success', 'Selected resource category statuses updated.');
    }

    public function bulkDestroy(BulkDeleteResourceCategoryRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('categories'), $request->user());

        return back()->with('success', 'Selected resource categories deleted.');
    }
}
