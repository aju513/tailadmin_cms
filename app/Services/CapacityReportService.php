<?php

namespace App\Services;

use App\Models\CapacityReport;
use App\Repositories\Contracts\CapacityReportRepositoryInterface;
use App\Services\Frontend\FrontendCache;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CapacityReportService
{
    public function __construct(private readonly CapacityReportRepositoryInterface $reports, private readonly FrontendCache $cache) {}

    public function index(array $filters): LengthAwarePaginator
    {
        return $this->reports->paginate($filters);
    }

    public function fiscalYearOptions(?CapacityReport $report = null, bool $includeSaved = false): array
    {
        $years = config('settings.fiscal_years', []);
        if ($report) {
            $years[] = $report->fiscal_year;
        }
        if ($includeSaved) {
            $years = [...$years, ...$this->reports->fiscalYears()];
        }
        $years = array_values(array_unique($years));
        rsort($years, SORT_NATURAL);

        return array_combine($years, $years);
    }

    public function newRecord(): CapacityReport
    {
        $rows = array_map(fn ($label) => ['key' => $label, 'value' => null], array_values(config('settings.capacity_reports.metrics')));

        return new CapacityReport(['development' => $rows, 'collaboration' => $rows]);
    }

    public function publicReports(): array
    {
        return $this->reports->all()->map(fn ($report) => ['year' => $report->fiscal_year, 'development' => $report->development, 'collaboration' => $report->collaboration])->all();
    }

    public function save(array $data, Authenticatable $actor, ?CapacityReport $report = null): CapacityReport
    {
        Gate::forUser($actor)->authorize($report ? 'capacity-reports.edit' : 'capacity-reports.create');
        try {
            $saved = DB::transaction(function () use ($data, $actor, $report): CapacityReport {
                $report = $report ? $this->reports->lock($report) : null;
                if ($this->reports->yearExists($data['fiscal_year'], $report)) {
                    throw ValidationException::withMessages(['fiscal_year' => 'A capacity report already exists for this fiscal year.']);
                }
                $values = ['fiscal_year' => $data['fiscal_year']];
                foreach (['development', 'collaboration'] as $group) {
                    $values[$group] = array_map(fn ($row) => ['key' => trim($row['key']), 'value' => isset($row['value']) && $row['value'] !== '' ? (int) $row['value'] : null], array_values($data[$group]));
                }
                $values['created_by'] = $report?->created_by ?? $actor->getAuthIdentifier();
                $values['updated_by'] = $actor->getAuthIdentifier();
                $saved = $report ? $this->reports->update($report, $values) : $this->reports->create($values);
                activity('content')->causedBy($actor)->performedOn($saved)->event($report ? 'capacity-report.updated' : 'capacity-report.created')
                    ->withProperties(['fiscal_year' => $saved->fiscal_year])->log('Capacity report saved');

                return $saved;
            });
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['fiscal_year' => 'A capacity report already exists for this fiscal year.']);
        }
        $this->cache->clear();

        return $saved;
    }

    public function delete(CapacityReport $report, Authenticatable $actor): void
    {
        Gate::forUser($actor)->authorize('capacity-reports.delete');
        DB::transaction(function () use ($report, $actor): void {
            $report = $this->reports->lock($report);
            activity('content')->causedBy($actor)->performedOn($report)->event('capacity-report.deleted')
                ->withProperties(['fiscal_year' => $report->fiscal_year])->log('Capacity report deleted');
            $this->reports->delete($report);
        });
        $this->cache->clear();
    }
}
