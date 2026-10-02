<?php

namespace App\Services;

use App\Repositories\Contracts\NoticeCategoryRepositoryInterface;

/** Read-only legacy category support until public notice listings use sections. */
class NoticeCategoryService
{
    public function __construct(private readonly NoticeCategoryRepositoryInterface $categories) {}

    public function options(bool $activeOnly = false): array
    {
        return $this->categories->options($activeOnly);
    }
}
