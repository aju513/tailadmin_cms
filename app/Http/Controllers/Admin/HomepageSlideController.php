<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\HomepageSlide\DeleteHomepageSlideRequest;
use App\Http\Requests\HomepageSlide\StoreHomepageSlideRequest;
use App\Http\Requests\HomepageSlide\UpdateHomepageSlideRequest;
use App\Models\HomepageSlide;
use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use App\Services\HomepageSlideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomepageSlideController extends Controller
{
    public function __construct(private readonly HomepageSlideRepositoryInterface $slides, private readonly HomepageSlideService $service) {}

    public function index(): View
    {
        return view('pages.admin.homepage-slides.index', ['slides' => $this->slides->paginateForIndex(), 'title' => 'Homepage Slides']);
    }

    public function create(): View
    {
        return view('pages.admin.homepage-slides.create', ['slide' => new HomepageSlide(['status' => ContentStatus::Draft]), 'title' => 'Create Homepage Slide']);
    }

    public function store(StoreHomepageSlideRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.homepage-slides.index')->with('success', 'Homepage slide created.');
    }

    public function edit(HomepageSlide $homepageSlide): View
    {
        return view('pages.admin.homepage-slides.edit', ['slide' => $homepageSlide, 'title' => 'Edit Homepage Slide']);
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
}
