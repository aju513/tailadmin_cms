<?php

namespace App\Services;

use App\Models\HomepageContent;
use App\Repositories\Contracts\HomepageContentRepositoryInterface;
use App\Services\Frontend\FrontendCache;
use App\Services\Frontend\SafeHtml;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class HomepageContentService
{
    public function __construct(
        private readonly HomepageContentRepositoryInterface $content,
        private readonly SiteSettingService $settings,
        private readonly MediaAssetService $media,
        private readonly SafeHtml $html,
        private readonly FrontendCache $cache,
    ) {}

    public function editor(): HomepageContent
    {
        return $this->content->find() ?? new HomepageContent($this->defaults());
    }

    private function defaults(): array
    {
        $settings = $this->settings->all();

        return [
            'key' => HomepageContent::KEY,
            'title' => ['en' => $settings['about_title'] ?: $settings['site_name']],
            'subtitle' => ['en' => 'Our About Us'],
            'body' => ['en' => $settings['about_description']],
            'meta_description' => $settings['meta_description'],
        ];
    }

    public function save(array $data, Authenticatable $actor): HomepageContent
    {
        Gate::forUser($actor)->authorize('homepage.edit');
        $uploads = [];

        try {
            $saved = DB::transaction(function () use ($data, $actor, &$uploads): HomepageContent {
                $content = $this->content->lockForSave($this->defaults());
                $removeIds = $data['remove_gallery_ids'] ?? [];
                $images = $data['gallery_images'] ?? [];
                $this->content->removeGalleryImages($content, $removeIds);
                if ($this->content->galleryCount($content) + count($images) > config('settings.homepage.gallery_limit')) {
                    throw ValidationException::withMessages(['gallery_images' => 'The homepage gallery can contain at most '.config('settings.homepage.gallery_limit').' images.']);
                }

                $attributes = [
                    'meta_title' => filled($data['meta_title'] ?? null) ? $data['meta_title'] : $data['translations']['en']['title'],
                    'meta_keywords' => $data['meta_keywords'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'created_by' => $content->created_by ?? $actor->getAuthIdentifier(),
                    'updated_by' => $actor->getAuthIdentifier(),
                ];
                foreach (['title', 'subtitle', 'body'] as $field) {
                    $attributes[$field] = $content->getTranslations($field);
                    foreach ($data['translations'] as $language => $translation) {
                        if (is_array($translation)) {
                            $value = $translation[$field] ?? null;
                            $attributes[$field][$language] = $field === 'body' ? $this->html->clean($value) : ($value ?? '');
                        }
                    }
                }

                if ($data['remove_social_media_image'] ?? false) {
                    $attributes['social_media_id'] = null;
                }
                if ($data['social_media_image'] ?? null) {
                    $asset = $this->media->store($data['social_media_image'], $actor, $attributes['title']['en'], $data['social_media_alt_text'] ?? $attributes['title']['en']);
                    $uploads[] = $asset;
                    $attributes['social_media_id'] = $asset->id;
                }
                $saved = $this->content->update($content, $attributes);
                foreach ($images as $file) {
                    $asset = $this->media->store($file, $actor, $attributes['title']['en'], $attributes['title']['en']);
                    $uploads[] = $asset;
                    $this->content->addGalleryImage($saved, $asset->id);
                }
                activity('content')->causedBy($actor)->performedOn($saved)->event('homepage.updated')
                    ->withProperties(['homepage_id' => $saved->id, 'gallery_added' => count($images), 'gallery_removed' => $removeIds])
                    ->log('Homepage content updated');

                return $saved;
            });
        } catch (Throwable $exception) {
            foreach ($uploads as $asset) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
            throw $exception;
        }
        $this->cache->clear();

        return $saved;
    }
}
