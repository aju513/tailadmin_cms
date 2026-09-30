<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceCategory\CreateResourceCategoryRequest;
use App\Http\Requests\ResourceCategory\DeleteResourceCategoryRequest;
use App\Http\Requests\ResourceCategory\EditResourceCategoryRequest;
use App\Http\Requests\ResourceCategory\IndexResourceCategoryRequest;
use App\Http\Requests\ResourceCategory\StoreResourceCategoryRequest;
use App\Http\Requests\ResourceCategory\UpdateResourceCategoryRequest;
use App\Models\ResourceCategory;
use App\Services\ResourceCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResourceCategoryController extends Controller
{
    public function __construct(private readonly ResourceCategoryService $service) {}

    public function index(IndexResourceCategoryRequest $request): View
    {
        return view('pages.admin.resource-categories.index', ['records' => $this->service->index($request->validated()), 'title' => 'Resource Categories']);
    }

    public function create(CreateResourceCategoryRequest $request): View
    {
        return view('pages.admin.resource-categories.create', ['record' => $this->service->newRecord(), 'title' => 'Add Resource Category']);
    }

    public function store(StoreResourceCategoryRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('resource-categories.manage') ? 'admin.resource-categories.index' : 'admin.resource-categories.create')
            ->with('success', 'Resource category created.');
    }

    public function edit(EditResourceCategoryRequest $request, ResourceCategory $resourceCategory): View
    {
        return view('pages.admin.resource-categories.edit', ['record' => $this->service->details($resourceCategory), 'title' => 'Edit Resource Category']);
    }

    public function update(UpdateResourceCategoryRequest $request, ResourceCategory $resourceCategory): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $resourceCategory);

        return redirect()->route('admin.resource-categories.edit', $record)->with('success', 'Resource category updated.');
    }

    public function destroy(DeleteResourceCategoryRequest $request, ResourceCategory $resourceCategory): RedirectResponse
    {
        $this->service->delete($resourceCategory, $request->user());

        return back()->with('success', 'Resource category deleted.');
    }
}
