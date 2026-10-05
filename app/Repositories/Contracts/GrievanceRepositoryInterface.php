<?php

namespace App\Repositories\Contracts;

use App\Models\Grievance;
use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GrievanceRepositoryInterface
{
    public function publishedPage(int $id, bool $lock = false): Page;

    public function create(array $data): Grievance;

    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id): Grievance;
}
