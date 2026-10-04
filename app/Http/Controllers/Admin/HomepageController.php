<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Homepage\EditHomepageRequest;
use App\Http\Requests\Admin\Homepage\UpdateHomepageRequest;
use App\Services\HomepageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function __construct(private readonly HomepageContentService $service) {}

    public function edit(EditHomepageRequest $request): View
    {
        return view('admin.pages.homepage.edit', ['content' => $this->service->editor(), 'title' => 'Homepage']);
    }

    public function update(UpdateHomepageRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.homepage.edit')->with('success', 'Homepage content saved.');
    }
}
