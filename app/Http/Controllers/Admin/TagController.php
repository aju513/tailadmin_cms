<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\DeleteTagRequest;
use App\Http\Requests\Content\IndexTagRequest;
use App\Http\Requests\Content\StoreTagRequest;
use App\Http\Requests\Content\UpdateTagRequest;
use App\Models\ContentTag;
use App\Repositories\Contracts\ContentRepositoryInterface;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function __construct(private readonly ContentRepositoryInterface $tags, private readonly TagService $service) {}

    public function index(IndexTagRequest $request): View
    {
        return view('pages.admin.content.index', ['items' => $this->tags->paginate($request->validated()), 'title' => 'Tags', 'resource' => 'tags', 'resourceLabel' => 'Tag']);
    }

    public function create(): View
    {
        return view('pages.admin.content.create', ['item' => new ContentTag(['status' => true]), 'title' => 'Create Tag', 'resource' => 'tags', 'resourceLabel' => 'Tag']);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.tags.index')->with('success', 'Tag created.');
    }

    public function edit(ContentTag $tag): View
    {
        return view('pages.admin.content.edit', ['item' => $tag, 'title' => 'Edit Tag', 'resource' => 'tags', 'resourceLabel' => 'Tag']);
    }

    public function update(UpdateTagRequest $request, ContentTag $tag): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $tag);

        return redirect()->route('admin.tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(DeleteTagRequest $request, ContentTag $tag): RedirectResponse
    {
        $this->service->delete($tag, $request->user());

        return back()->with('success', 'Tag deleted.');
    }
}
