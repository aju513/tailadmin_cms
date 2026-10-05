<?php

namespace App\Repositories\Contracts;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface FrontendRepositoryInterface
{
    public function page(string $path): Page;

    public function pages(): Collection;

    public function findPage(string $path): ?Page;

    public function listing(string $type, array $filters = []): LengthAwarePaginator;

    public function detail(string $type, string $key): Model;

    public function recent(string $type, int $limit = 4): Collection;

    public function teamCategories(): Collection;

    public function search(string $term, array $filters = []): array;

    public function sitemapCount(string $type): int;

    public function sitemapRecords(string $type, int $chunk): Collection;

    public function mediaForOptimization(): \Illuminate\Support\LazyCollection;
}
