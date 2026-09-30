<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Video\CreateVideoRequest;
use App\Http\Requests\Video\DeleteVideoRequest;
use App\Http\Requests\Video\EditVideoRequest;
use App\Http\Requests\Video\IndexVideoRequest;
use App\Http\Requests\Video\StoreVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __construct(private readonly VideoService $service) {}

    public function index(IndexVideoRequest $request): View
    {
        return view('pages.admin.videos.index', ['records' => $this->service->index($request->validated()), 'title' => 'Videos']);
    }

    public function create(CreateVideoRequest $request): View
    {
        return view('pages.admin.videos.create', ['record' => $this->service->newRecord(), 'title' => 'Add Video']);
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('videos.manage') ? 'admin.videos.index' : 'admin.videos.create')
            ->with('success', 'Video created.');
    }

    public function edit(EditVideoRequest $request, Video $video): View
    {
        return view('pages.admin.videos.edit', ['record' => $this->service->details($video), 'title' => 'Edit Video']);
    }

    public function update(UpdateVideoRequest $request, Video $video): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $video);

        return redirect()->route('admin.videos.edit', $record)->with('success', 'Video updated.');
    }

    public function destroy(DeleteVideoRequest $request, Video $video): RedirectResponse
    {
        $this->service->delete($video, $request->user());

        return back()->with('success', 'Video deleted. Uploaded files remain in the Media Library.');
    }
}
