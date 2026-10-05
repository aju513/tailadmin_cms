<?php

namespace App\Http\Requests\Admin\CapacityReport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCapacityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('capacity-reports.create');
    }

    protected function prepareForValidation(): void
    {
        foreach (['development', 'collaboration'] as $group) {
            if (is_array($this->input($group))) {
                $rows = array_map(function ($row) {
                    if (is_array($row) && is_string($row['key'] ?? null)) {
                        $row['key'] = trim($row['key']);
                    }

                    return $row;
                }, $this->input($group));
                $this->merge([$group => $rows]);
            }
        }
    }

    public function rules(): array
    {
        $report = $this->route('capacityReport');
        $years = config('settings.fiscal_years', []);
        if ($report) {
            $years[] = $report->fiscal_year;
        }
        $rules = ['fiscal_year' => ['required', 'string', 'max:20', Rule::in($years), Rule::unique('capacity_reports', 'fiscal_year')->ignore($report)]];
        foreach (['development', 'collaboration'] as $group) {
            $rules[$group] = ['required', 'array', 'list', 'min:1', 'max:'.config('settings.capacity_reports.max_rows')];
            $rules[$group.'.*'] = ['required', 'array:key,value'];
            $rules[$group.'.*.key'] = ['required', 'string', 'max:160', 'distinct:ignore_case'];
            $rules[$group.'.*.value'] = ['nullable', 'integer', 'min:0', 'max:'.config('settings.capacity_reports.max_value')];
        }

        return $rules;
    }

    public function messages(): array
    {
        return ['fiscal_year.in' => 'Select a fiscal year configured in settings.', 'fiscal_year.unique' => 'A capacity report already exists for this fiscal year.', '*.key.distinct' => 'Use a different key for each row in the same contribution group.'];
    }
}
