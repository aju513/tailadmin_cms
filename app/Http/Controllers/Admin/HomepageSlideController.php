<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageSlide\CreateHomepageSlideRequest;
use App\Http\Requests\Admin\HomepageSlide\DeleteHomepageSlideRequest;
use App\Http\Requests\Admin\HomepageSlide\EditHomepageSlideRequest;
use App\Http\Requests\Admin\HomepageSlide\IndexHomepageSlideRequest;
use App\Http\Requests\Admin\HomepageSlide\OrderHomepageSlideRequest;
use App\Http\Requests\Admin\HomepageSlide\StoreHomepageSlideRequest;
use App\Http\Requests\Admin\HomepageSlide\UpdateHomepageSlideRequest;
use App\Models\HomepageSlide;
use App\Services\HomepageSlideService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomepageSlideController extends Controller
{
    public function __construct(private readonly HomepageSlideService $service) {}

    public function index(IndexHomepageSlideRequest $request): View
    {
        return view('admin.pages.homepage-slides.index', ['slides' => $this->service->index(), 'title' => 'Homepage Slides']);
    }

    public function create(CreateHomepageSlideRequest $request): View
    {
        return view('admin.pages.homepage-slides.create', ['slide' => $this->service->newRecord(), 'title' => 'Create Homepage Slide']);
    }

    public function store(StoreHomepageSlideRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.homepage-slides.index')->with('success', 'Homepage slide created.');
    }

    public function edit(EditHomepageSlideRequest $request, HomepageSlide $homepageSlide): View
    {
        return view('admin.pages.homepage-slides.edit', ['slide' => $this->service->details($homepageSlide), 'title' => 'Edit Homepage Slide']);
    }

    public function order(OrderHomepageSlideRequest $request): JsonResponse
    {
        $this->service->reorder($request->validated('records'), $request->validated('original_order'), $request->user());

        return response()->json(['message' => 'Home slide order updated.']);
    }

    public function update(UpdateHomepageSlideRequest $request, HomepageSlide $homepageSlide): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $homepageSlide);

        return redirect()->route('admin.homepage-slides.index')->with('success', 'Homepage slide updated.');
    }

    public function destroy(DeleteHomepageSlideRequest $request, HomepageSlide $homepageSlide): RedirectResponse
    {
        $this->service->delete($homepageSlide, $request->user());

        return back()->with('success', 'Homepage slide deleted.');
    }

    public function bulkStatus(\App\Http\Requests\Admin\HomepageSlide\BulkHomepageSlideStatusRequest $request): RedirectResponse|JsonResponse
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

    public function bulkDestroy(\App\Http\Requests\Admin\HomepageSlide\BulkHomepageSlideDeleteRequest $request): RedirectResponse
    {
        $this->service->bulkDelete($request->validated('records'), $request->user());

        return back()->with('success', 'Selected records deleted.');
    }
}
