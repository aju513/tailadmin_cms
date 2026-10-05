<?php

namespace App\Repositories\Contracts;

use App\Models\CapacityReport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CapacityReportRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;

    public function all(): Collection;

    public function fiscalYears(): array;

    public function yearExists(string $year, ?CapacityReport $except = null): bool;

    public function lock(CapacityReport $report): CapacityReport;

    public function create(array $data): CapacityReport;

    public function update(CapacityReport $report, array $data): CapacityReport;

    public function delete(CapacityReport $report): void;
}
