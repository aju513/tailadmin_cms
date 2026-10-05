<?php

namespace App\Repositories\Http;

use App\Repositories\Contracts\PublicTrainingRepositoryInterface;
use Illuminate\Support\Facades\Http;
use UnexpectedValueException;

class TimsTrainingRepository implements PublicTrainingRepositoryInterface
{
    public function ongoing(int $limit): array
    {
        $response = Http::acceptJson()->connectTimeout(2)
            ->timeout(config('services.tims.timeout_seconds'))
            ->get(rtrim(config('services.tims.base_url'), '/').'/api/v1/trainings', [
                'status' => 'ongoing', 'limit' => $limit,
            ])->throw();
        $payload = $response->json();

        if (! is_array($payload) || ($payload['status'] ?? null) !== true
            || ! is_array($payload['data'] ?? null) || ! array_is_list($payload['data'])) {
            throw new UnexpectedValueException('Invalid TIMS training catalogue response.');
        }

        return $payload['data'];
    }
}
