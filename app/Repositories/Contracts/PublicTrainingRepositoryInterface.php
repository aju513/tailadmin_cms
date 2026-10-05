<?php

namespace App\Repositories\Contracts;

interface PublicTrainingRepositoryInterface
{
    public function ongoing(int $limit): array;
}
