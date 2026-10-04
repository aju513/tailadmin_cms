<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Video\CreateVideoRequest;
use App\Http\Requests\Admin\Video\DeleteVideoRequest;
use App\Http\Requests\Admin\Video\EditVideoRequest;
use App\Http\Requests\Admin\Video\IndexVideoRequest;
use App\Http\Requests\Admin\Video\StoreVideoRequest;
use App\Http\Requests\Admin\Video\UpdateVideoRequest;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __construct(private readonly VideoService $service) {}

    public function index(IndexVideoRequest $request): View
    {
        return view('admin.pages.videos.index', ['records' => $this->service->index($request->validated()), 'title' => 'Videos']);
    }

    public function create(CreateVideoRequest $request): View
    {
        return view('admin.pages.videos.create', ['record' => $this->service->newRecord(), 'title' => 'Add Video']);
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route($request->user()->can('videos.manage') ? 'admin.videos.index' : 'admin.videos.create')
            ->with('success', 'Video created.');
    }

    public function edit(EditVideoRequest $request, Video $video): View
    {
        return view('admin.pages.videos.edit', ['record' => $this->service->details($video), 'title' => 'Edit Video']);
    }

    public function update(UpdateVideoRequest $request, Video $video): RedirectResponse
    {
        $record = $this->service->save($request->validated(), $request->user(), $video);

        return redirect()->route('admin.videos.edit', $record)->with('success', 'Video updated.');
    }

    public function destroy(DeleteVideoRequest $request, Video $video): RedirectResponse
    {
        $this->service->delete($video, $request->user());

        return back()->with('success', 'Video deleted. Uploaded files are preserved.');
    }

    public function bulkStatus(\App\Http\Requests\Admin\Video\BulkVideoStatusRequest $request): RedirectResponse|JsonResponse
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

    public function bulkDestroy(\App\Http\Requests\Admin\Video\BulkVideoDeleteRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('records'), $request->user());

        return back()->with('success', 'Selected records deleted.');
    }
}
