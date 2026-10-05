<?php

namespace App\Repositories\Eloquent;

use App\Models\CapacityReport;
use App\Repositories\Contracts\CapacityReportRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CapacityReportRepository implements CapacityReportRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return CapacityReport::query()->when($filters['fiscal_year'] ?? null, fn ($query, $year) => $query->where('fiscal_year', $year))
            ->orderByDesc('fiscal_year')->orderByDesc('id')->paginate(15)->withQueryString();
    }

    public function all(): Collection
    {
        return CapacityReport::query()->orderByDesc('fiscal_year')->orderByDesc('id')->get();
    }

    public function fiscalYears(): array
    {
        return CapacityReport::query()->orderByDesc('fiscal_year')->pluck('fiscal_year')->all();
    }

    public function yearExists(string $year, ?CapacityReport $except = null): bool
    {
        return CapacityReport::query()->where('fiscal_year', $year)->when($except, fn ($query) => $query->whereKeyNot($except->id))->exists();
    }

    public function lock(CapacityReport $report): CapacityReport
    {
        return CapacityReport::query()->lockForUpdate()->findOrFail($report->id);
    }

    public function create(array $data): CapacityReport
    {
        return CapacityReport::query()->create($data);
    }

    public function update(CapacityReport $report, array $data): CapacityReport
    {
        $report->update($data);

        return $report;
    }

    public function delete(CapacityReport $report): void
    {
        $report->delete();
    }
}
