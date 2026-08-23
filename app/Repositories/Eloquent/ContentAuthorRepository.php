<?php

namespace App\Repositories\Eloquent;

use App\Models\ContentAuthor;
use App\Repositories\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ContentAuthorRepository implements ContentRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return ContentAuthor::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))->orderBy('name')->paginate(15)->withQueryString();
    }

    public function create(array $data): Model
    {
        return ContentAuthor::query()->create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->refresh();
    }

    public function delete(Model $model): void
    {
        $model->delete();
    }
}
