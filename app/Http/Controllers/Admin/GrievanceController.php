<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Grievances\IndexGrievanceRequest;
use App\Http\Requests\Admin\Grievances\ShowGrievanceRequest;
use App\Services\GrievanceService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrievanceController extends Controller
{
    public function __construct(private readonly GrievanceService $grievances) {}

    public function index(IndexGrievanceRequest $request): View
    {
        return view('admin.pages.grievances.index', ['title' => 'Grievances', 'items' => $this->grievances->paginate($request->validated())]);
    }

    public function show(ShowGrievanceRequest $request, int $grievanceId): View
    {
        return view('admin.pages.grievances.show', ['title' => 'Grievance Details', 'grievance' => $this->grievances->find($grievanceId)]);
    }

    public function download(ShowGrievanceRequest $request, int $grievanceId): StreamedResponse
    {
        return $this->grievances->download($grievanceId);
    }
}
