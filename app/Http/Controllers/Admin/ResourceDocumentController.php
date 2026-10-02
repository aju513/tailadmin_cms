<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceDocument\BulkDeleteResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\BulkResourceStatusRequest;
use App\Http\Requests\ResourceDocument\CreateResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\DeleteResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\EditResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\IndexResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\OrderResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\PublishResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\StoreResourceDocumentRequest;
use App\Http\Requests\ResourceDocument\UpdateResourceDocumentRequest;
use App\Models\ResourceDocument;
use App\Services\ResourceDocumentService;
use Illuminate\Http\JsonResponse;
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

    public function publish(PublishResourceDocumentRequest $request, ResourceDocument $resourceDocument): RedirectResponse
    {
        $this->service->publish($resourceDocument, $request->user());

        return back()->with('success', 'Resource published.');
    }

    public function unpublish(PublishResourceDocumentRequest $request, ResourceDocument $resourceDocument): RedirectResponse
    {
        $this->service->unpublish($resourceDocument, $request->user());

        return back()->with('success', 'Resource unpublished.');
    }

    public function bulkStatus(BulkResourceStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkChangeStatus($request->validated('resources'), \App\Enums\ContentStatus::from($request->validated('status')), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => (string) $request->validated('status')], $request->validated('resources')),
            ]);
        }

        return back()->with('success', 'Selected resource statuses updated.');
    }

    public function bulkDestroy(BulkDeleteResourceDocumentRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('resources'), $request->user());

        return back()->with('success', 'Selected resources deleted.');
    }

    public function order(OrderResourceDocumentRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->service->reorder($request->validated('resources'));

        return response()->json(['message' => 'Resource order updated.']);
    }
}
