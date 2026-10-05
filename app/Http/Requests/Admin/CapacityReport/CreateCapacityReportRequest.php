<?php

namespace App\Http\Requests\Admin\CapacityReport;

use Illuminate\Foundation\Http\FormRequest;

class CreateCapacityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('capacity-reports.create');
    }

    public function rules(): array
    {
        return [];
    }
}
