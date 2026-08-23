<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\DeleteMediaRequest;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Models\MediaAsset;
use App\Repositories\Contracts\MediaAssetRepositoryInterface;
use App\Services\MediaAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(private readonly MediaAssetRepositoryInterface $media, private readonly MediaAssetService $service) {}

    public function index(): View
    {
        return view('pages.admin.media.index', ['media' => $this->media->all(), 'title' => 'Media Library']);
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $this->service->store($request->file('file'), $request->user(), $request->validated('title'), $request->validated('alt_text'));

        return back()->with('success', 'Media uploaded.');
    }

    public function destroy(DeleteMediaRequest $request, MediaAsset $media): RedirectResponse
    {
        $this->service->delete($media);

        return back()->with('success', 'Media deleted.');
    }
}
