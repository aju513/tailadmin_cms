<?php

namespace App\Services;

use App\Models\HomepageSlide;
use App\Repositories\Contracts\HomepageSlideRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class HomepageSlideService
{
    public function __construct(private readonly HomepageSlideRepositoryInterface $slides, private readonly MediaAssetService $media) {}

    public function save(array $data, Authenticatable $actor, ?HomepageSlide $slide = null): HomepageSlide
    {
        return DB::transaction(function () use ($data, $actor, $slide): HomepageSlide {
            if ($data['image'] ?? null) {
                $asset = $this->media->store($data['image'], $actor, $data['title'], $data['title']);
                $data['media_id'] = $asset->id;
            }
            unset($data['image']);
            $data['updated_by'] = $actor->getAuthIdentifier();
            $data['created_by'] ??= $actor->getAuthIdentifier();
            $saved = $slide ? $this->slides->update($slide, $data) : $this->slides->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($slide ? 'homepage-slide.updated' : 'homepage-slide.created')->log($slide ? 'Homepage slide updated' : 'Homepage slide created');

            return $saved;
        });
    }

    public function delete(HomepageSlide $slide, Authenticatable $actor): void
    {
        $this->slides->delete($slide);
        activity('content')->causedBy($actor)->event('homepage-slide.deleted')->withProperties(['slide_id' => $slide->id])->log('Homepage slide deleted');
    }
}
