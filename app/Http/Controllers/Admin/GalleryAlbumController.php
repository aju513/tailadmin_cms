<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GalleryAlbum\CreateGalleryAlbumRequest;
use App\Http\Requests\GalleryAlbum\DeleteGalleryAlbumRequest;
use App\Http\Requests\GalleryAlbum\EditGalleryAlbumRequest;
use App\Http\Requests\GalleryAlbum\IndexGalleryAlbumRequest;
use App\Http\Requests\GalleryAlbum\StoreGalleryAlbumRequest;
use App\Http\Requests\GalleryAlbum\UpdateGalleryAlbumRequest;
use App\Models\GalleryAlbum;
use App\Services\GalleryAlbumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GalleryAlbumController extends Controller
{
    public function __construct(private readonly GalleryAlbumService $service) {}

    public function index(IndexGalleryAlbumRequest $request): View
    {
        return view('pages.admin.gallery.index', ['records' => $this->service->index($request->validated()), 'title' => 'Galleries']);
    }

    public function create(CreateGalleryAlbumRequest $request): View
    {
        return view('pages.admin.gallery.create', ['record' => $this->service->newRecord(), 'title' => 'Add Gallery']);
    }

    public function store(StoreGalleryAlbumRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('gallery.manage') ? 'admin.gallery.index' : 'admin.gallery.create')
            ->with('success', 'Gallery created.');
    }

    public function edit(EditGalleryAlbumRequest $request, GalleryAlbum $galleryAlbum): View
    {
        return view('pages.admin.gallery.edit', ['record' => $this->service->details($galleryAlbum), 'title' => 'Edit Gallery']);
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
}
