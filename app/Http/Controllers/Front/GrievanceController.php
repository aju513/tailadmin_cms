<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\StoreGrievanceRequest;
use App\Services\GrievanceService;
use Illuminate\Http\RedirectResponse;

class GrievanceController extends Controller
{
    public function __construct(private readonly GrievanceService $grievances) {}

    public function store(StoreGrievanceRequest $request, int $pageId): RedirectResponse
    {
        $data = $request->validated();
        $grievance = $this->grievances->submit($pageId, $data);

        return redirect()->route('public.page', ['path' => $grievance->page_path, 'lang' => $data['lang'] ?? 'en'])
            ->with('grievance_success', 'Your grievance has been submitted. Reference: '.$grievance->reference);
    }
}
