<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\DeleteAuthorRequest;
use App\Http\Requests\Admin\Content\IndexAuthorRequest;
use App\Http\Requests\Admin\Content\StoreAuthorRequest;
use App\Http\Requests\Admin\Content\UpdateAuthorRequest;
use App\Models\ContentAuthor;
use App\Repositories\Contracts\ContentRepositoryInterface;
use App\Services\AuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function __construct(private readonly ContentRepositoryInterface $authors, private readonly AuthorService $service) {}

    public function index(IndexAuthorRequest $request): View
    {
        return view('admin.pages.content.index', ['items' => $this->authors->paginate($request->validated()), 'title' => 'Authors', 'resource' => 'authors', 'resourceLabel' => 'Author']);
    }

    public function create(): View
    {
        return view('admin.pages.content.create', ['item' => new ContentAuthor(['status' => true]), 'title' => 'Create Author', 'resource' => 'authors', 'resourceLabel' => 'Author']);
    }

    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.authors.index')->with('success', 'Author created.');
    }

    public function edit(ContentAuthor $author): View
    {
        return view('admin.pages.content.edit', ['item' => $author, 'title' => 'Edit Author', 'resource' => 'authors', 'resourceLabel' => 'Author']);
    }

    public function update(UpdateAuthorRequest $request, ContentAuthor $author): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $author);

        return redirect()->route('admin.authors.index')->with('success', 'Author updated.');
    }

    public function destroy(DeleteAuthorRequest $request, ContentAuthor $author): RedirectResponse
    {
        $this->service->delete($author, $request->user());

        return back()->with('success', 'Author deleted.');
    }
}
