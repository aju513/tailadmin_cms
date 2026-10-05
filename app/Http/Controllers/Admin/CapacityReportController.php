<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CapacityReport\CreateCapacityReportRequest;
use App\Http\Requests\Admin\CapacityReport\DeleteCapacityReportRequest;
use App\Http\Requests\Admin\CapacityReport\EditCapacityReportRequest;
use App\Http\Requests\Admin\CapacityReport\IndexCapacityReportRequest;
use App\Http\Requests\Admin\CapacityReport\StoreCapacityReportRequest;
use App\Http\Requests\Admin\CapacityReport\UpdateCapacityReportRequest;
use App\Models\CapacityReport;
use App\Services\CapacityReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CapacityReportController extends Controller
{
    public function __construct(private readonly CapacityReportService $service) {}

    public function index(IndexCapacityReportRequest $request): View
    {
        return view('admin.pages.capacity-reports.index', ['records' => $this->service->index($request->validated()), 'fiscalYears' => $this->service->fiscalYearOptions(includeSaved: true), 'title' => 'Capacity Reports']);
    }

    public function create(CreateCapacityReportRequest $request): View
    {
        return view('admin.pages.capacity-reports.form', ['report' => $this->service->newRecord(), 'fiscalYears' => $this->service->fiscalYearOptions(), 'title' => 'Add Capacity Report']);
    }

    public function store(StoreCapacityReportRequest $request): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user());

        return redirect()->route('admin.capacity-reports.index')->with('success', 'Capacity report saved.');
    }

    public function edit(EditCapacityReportRequest $request, CapacityReport $capacityReport): View
    {
        return view('admin.pages.capacity-reports.form', ['report' => $capacityReport, 'fiscalYears' => $this->service->fiscalYearOptions($capacityReport), 'title' => 'Edit Capacity Report']);
    }

    public function update(UpdateCapacityReportRequest $request, CapacityReport $capacityReport): RedirectResponse
    {
        $this->service->save($request->validated(), $request->user(), $capacityReport);

        return redirect()->route('admin.capacity-reports.index')->with('success', 'Capacity report updated.');
    }

    public function destroy(DeleteCapacityReportRequest $request, CapacityReport $capacityReport): RedirectResponse
    {
        $this->service->delete($capacityReport, $request->user());

        return redirect()->route('admin.capacity-reports.index')->with('success', 'Capacity report deleted.');
    }
}
