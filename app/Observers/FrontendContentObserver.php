<?php

namespace App\Observers;

use App\Services\Frontend\FrontendCache;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Model;

class FrontendContentObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Model $model): void
    {
        app(FrontendCache::class)->clear();
        if ($model instanceof \App\Models\MediaAsset) {
            try {
                app(\App\Services\Frontend\ImageService::class)->optimize($model);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }

    public function deleted(Model $model): void
    {
        app(FrontendCache::class)->clear();
        if ($model instanceof \App\Models\MediaAsset) {
            app(\App\Services\Frontend\ImageService::class)->remove($model);
        }
    }
}
