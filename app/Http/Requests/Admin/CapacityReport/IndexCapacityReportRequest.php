<?php

namespace App\Http\Requests\Admin\CapacityReport;

use Illuminate\Foundation\Http\FormRequest;

class IndexCapacityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('capacity-reports.manage');
    }

    public function rules(): array
    {
        return ['fiscal_year' => ['nullable', 'string', 'max:20'], 'page' => ['nullable', 'integer', 'min:1']];
    }
}
