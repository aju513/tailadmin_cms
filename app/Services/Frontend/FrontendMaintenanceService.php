<?php

namespace App\Services\Frontend;

use App\Repositories\Contracts\FrontendRepositoryInterface;

class FrontendMaintenanceService
{
    public function __construct(private readonly FrontendRepositoryInterface $content, private readonly ImageService $images, private readonly FrontendCache $cache) {}

    public function optimizeImages(): int
    {
        $count = 0;
        foreach ($this->content->mediaForOptimization() as $asset) {
            if ($this->images->optimize($asset)) {
                $count++;
            }
        }
        $this->cache->clear();

        return $count;
    }
}
