<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\DeleteCategoryRequest;
use App\Http\Requests\Admin\Content\IndexCategoryRequest;
use App\Http\Requests\Admin\Content\StoreCategoryRequest;
use App\Http\Requests\Admin\Content\UpdateCategoryRequest;
use App\Models\ContentCategory;
use App\Repositories\Contracts\ContentRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly ContentRepositoryInterface $categories, private readonly CategoryService $service) {}

    public function index(IndexCategoryRequest $request): View
    {
        return view('admin.pages.content.index', ['items' => $this->categories->paginate($request->validated()), 'title' => 'Categories', 'resource' => 'categories', 'resourceLabel' => 'Category']);
    }

    public function create(): View
    {
        return view('admin.pages.content.create', ['item' => new ContentCategory(['status' => true]), 'title' => 'Create Category', 'resource' => 'categories', 'resourceLabel' => 'Category']);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(ContentCategory $category): View
    {
        return view('admin.pages.content.edit', ['item' => $category, 'title' => 'Edit Category', 'resource' => 'categories', 'resourceLabel' => 'Category']);
    }

    public function update(UpdateCategoryRequest $request, ContentCategory $category): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(DeleteCategoryRequest $request, ContentCategory $category): RedirectResponse
    {
        $this->service->delete($category, $request->user());

        return back()->with('success', 'Category deleted.');
    }
}
