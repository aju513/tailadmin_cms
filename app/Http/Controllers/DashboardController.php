<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardRequest;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardRequest $request, DashboardService $dashboard): View
    {
        return view('pages.dashboard.ecommerce', [
            'title' => 'Dashboard',
            'dashboard' => $dashboard->overview((int) ($request->validated('days') ?? 90)),
        ]);
    }
}
