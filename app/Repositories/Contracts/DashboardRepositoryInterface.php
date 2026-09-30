<?php

namespace App\Repositories\Contracts;

interface DashboardRepositoryInterface
{
    public function cached(string $key): ?array;

    public function store(string $key, array $payload, int $seconds): void;
}
