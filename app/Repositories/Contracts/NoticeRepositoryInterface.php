<?php

namespace App\Repositories\Contracts;

use App\Models\Notice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NoticeRepositoryInterface
{
    public function paginateAdmin(array $filters): LengthAwarePaginator;

    public function paginatePublished(?int $categoryId = null, string $pageName = 'page'): LengthAwarePaginator;

    public function publishedBySlug(string $slug): Notice;

    public function lock(Notice $notice): Notice;

    public function details(Notice $notice): Notice;

    public function create(array $data): Notice;

    public function update(Notice $notice, array $data): Notice;

    public function delete(Notice $notice): void;

    public function slugExists(string $slug, ?Notice $except = null): bool;

    public function nextSortOrder(): int;
}
