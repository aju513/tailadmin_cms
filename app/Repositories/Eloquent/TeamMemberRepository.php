<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamMemberRepository implements TeamMemberRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return TeamMember::query()
            ->with('photoMedia')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            }))
            ->when(isset($filters['status']), fn ($query) => $query->where('is_active', $filters['status'] === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): TeamMember
    {
        return TeamMember::query()->create($data);
    }

    public function update(TeamMember $member, array $data): TeamMember
    {
        $member->update($data);

        return $member->refresh();
    }

    public function delete(TeamMember $member): void
    {
        $member->delete();
    }

    public function nextSortOrder(): int
    {
        return (int) TeamMember::query()->max('sort_order') + 1;
    }
}
