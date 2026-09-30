<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceDocument\CreateResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\DeleteResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\EditResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\IndexResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\StoreResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\UpdateResourceDocumentRequest;
use App\Models\ResourceDocument;
use App\Services\ResourceDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResourceDocumentController extends Controller
{
    public function __construct(private readonly ResourceDocumentService $service) {}

    public function index(IndexResourceDocumentRequest $request): View
    {
        return view('pages.admin.resources.index', ['records' => $this->service->index($request->validated()), 'title' => 'Resources']);
    }

    public function create(CreateResourceDocumentRequest $request): View
    {
        return view('pages.admin.resources.create', ['record' => $this->service->newRecord(), 'categories' => $this->service->categoryOptions(), 'title' => 'Add Resource']);
    }

    public function store(StoreResourceDocumentRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('resources.manage') ? 'admin.resources.index' : 'admin.resources.create')
            ->with('success', 'Resource created.');
    }

    public function edit(EditResourceDocumentRequest $request, ResourceDocument $resourceDocument): View
    {
        return view('pages.admin.resources.edit', ['record' => $this->service->details($resourceDocument), 'categories' => $this->service->categoryOptions(), 'title' => 'Edit Resource']);
    }

    public function update(UpdateResourceDocumentRequest $request, ResourceDocument $resourceDocument): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $resourceDocument);

        return redirect()->route('admin.resources.edit', $record)->with('success', 'Resource updated.');
    }

    public function destroy(DeleteResourceDocumentRequest $request, ResourceDocument $resourceDocument): RedirectResponse
    {
        $this->service->delete($resourceDocument, $request->user());

        return back()->with('success', 'Resource deleted. Uploaded files remain in the Media Library.');
    }
}
