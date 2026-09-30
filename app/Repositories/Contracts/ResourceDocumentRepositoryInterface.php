<?php

namespace App\Repositories\Contracts;

use App\Models\ResourceDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ResourceDocumentRepositoryInterface
{
    public function paginate(array $filters): LengthAwarePaginator;
    public function lock(ResourceDocument $record): ResourceDocument;
    public function find(int $id): ResourceDocument;
    public function slugExists(string $slug, ?ResourceDocument $record): bool;
    public function create(array $data): ResourceDocument;
    public function update(ResourceDocument $record, array $data): ResourceDocument;
    public function delete(ResourceDocument $record): void;

    public function published(?int $categoryId): LengthAwarePaginator;
    public function publicBySlug(string $slug): ResourceDocument;

}
