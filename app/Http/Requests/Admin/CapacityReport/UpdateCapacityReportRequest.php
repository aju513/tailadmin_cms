<?php

namespace App\Http\Requests\Admin\CapacityReport;

class UpdateCapacityReportRequest extends StoreCapacityReportRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('capacity-reports.edit');
    }
}
