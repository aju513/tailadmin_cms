<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSiteSettingsRequest;
use App\Services\SiteSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function __construct(private readonly SiteSettingService $settings) {}

    public function edit(): View
    {
        return view('pages.admin.settings.edit', ['settings' => $this->settings->all(), 'title' => 'Site Settings']);
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $this->settings->update($request->validated(), $request->user());

        return back()->with('success', 'Site settings updated.');
    }
}
