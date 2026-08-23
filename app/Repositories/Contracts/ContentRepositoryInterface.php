<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface ContentRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function create(array $data): Model;

    public function update(Model $model, array $data): Model;

    public function delete(Model $model): void;
}
