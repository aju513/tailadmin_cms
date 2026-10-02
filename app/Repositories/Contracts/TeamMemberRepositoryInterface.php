<?php

namespace App\Repositories\Contracts;

use App\Models\TeamMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TeamMemberRepositoryInterface
{
    public function paginate(array $filters = []): LengthAwarePaginator;

    public function create(array $data): TeamMember;

    public function update(TeamMember $member, array $data): TeamMember;

    public function delete(TeamMember $member): void;

    public function nextSortOrder(): int;
    public function findByIds(array $ids): Collection;
}
