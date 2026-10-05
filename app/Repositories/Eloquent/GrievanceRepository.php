<?php

namespace App\Repositories\Eloquent;

use App\Enums\ContentStatus;
use App\Enums\PageType;
use App\Models\Grievance;
use App\Models\Page;
use App\Repositories\Contracts\GrievanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GrievanceRepository implements GrievanceRepositoryInterface
{
    public function publishedPage(int $id, bool $lock = false): Page
    {
        return Page::query()->whereKey($id)->where('page_type', PageType::Grievance)->where('status', ContentStatus::Published)
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->when($lock, fn ($query) => $query->lockForUpdate())->firstOrFail();
    }

    public function create(array $data): Grievance
    {
        return Grievance::query()->create($data);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return Grievance::query()->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
            foreach (['reference', 'full_name', 'email', 'subject'] as $field) {
                $query->orWhere($field, 'like', '%'.$search.'%');
            }
        }))->latest('id')->paginate(15)->withQueryString();
    }

    public function find(int $id): Grievance
    {
        return Grievance::query()->findOrFail($id);
    }
}
