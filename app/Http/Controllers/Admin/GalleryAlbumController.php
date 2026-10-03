<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryAlbum\CreateGalleryAlbumRequest;
use App\Http\Requests\Admin\GalleryAlbum\DeleteGalleryAlbumRequest;
use App\Http\Requests\Admin\GalleryAlbum\EditGalleryAlbumRequest;
use App\Http\Requests\Admin\GalleryAlbum\IndexGalleryAlbumRequest;
use App\Http\Requests\Admin\GalleryAlbum\StoreGalleryAlbumRequest;
use App\Http\Requests\Admin\GalleryAlbum\UpdateGalleryAlbumRequest;
use App\Models\GalleryAlbum;
use App\Services\GalleryAlbumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GalleryAlbumController extends Controller
{
    public function __construct(private readonly GalleryAlbumService $service) {}

    public function index(IndexGalleryAlbumRequest $request): View
    {
        return view('admin.pages.gallery.index', ['records' => $this->service->index($request->validated()), 'title' => 'Galleries']);
    }

    public function create(CreateGalleryAlbumRequest $request): View
    {
        return view('admin.pages.gallery.create', ['record' => $this->service->newRecord(), 'title' => 'Add Gallery']);
    }

    public function store(StoreGalleryAlbumRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('gallery.manage') ? 'admin.gallery.index' : 'admin.gallery.create')
            ->with('success', 'Gallery created.');
    }

    public function edit(EditGalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): View
    {
        return view('admin.pages.gallery.edit', ['record' => $this->service->details($galleryAlbum), 'title' => 'Edit Gallery']);
    }

    public function update(UpdateGalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $galleryAlbum);

        return redirect()->route('admin.gallery.edit', $record)->with('success', 'Gallery updated.');
    }

    public function destroy(DeleteGalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): RedirectResponse
    {
        $this->service->delete($galleryAlbum, $request->user());

        return back()->with('success', 'Gallery deleted. Uploaded files remain in the Media Library.');
    }

    public function bulkStatus(\App\Http\Requests\Admin\GalleryAlbum\BulkGalleryAlbumStatusRequest $request): RedirectResponse|JsonResponse
    {
        $this->service->bulkStatus($request->validated('records'), \App\Enums\ContentStatus::from($request->validated('status')), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Selected statuses updated.',
                'records' => array_map(fn ($id) => ['id' => (int) $id, 'status' => (string) $request->validated('status')], $request->validated('records')),
            ]);
        }

        return back()->with('success', 'Selected publication statuses updated.');
    }

    public function bulkDestroy(\App\Http\Requests\Admin\GalleryAlbum\BulkGalleryAlbumDeleteRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('records'), $request->user());

        return back()->with('success', 'Selected records deleted.');
    }
}
