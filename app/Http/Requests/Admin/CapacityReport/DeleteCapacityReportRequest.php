<?php

namespace App\Http\Requests\Admin\CapacityReport;

class DeleteCapacityReportRequest extends CreateCapacityReportRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('capacity-reports.delete');
    }
}
