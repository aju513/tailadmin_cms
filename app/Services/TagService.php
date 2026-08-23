<?php

namespace App\Services;

use App\Models\ContentTag;
use App\Repositories\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagService
{
    public function __construct(private readonly ContentRepositoryInterface $tags) {}

    public function save(array $data, Authenticatable $actor, ?ContentTag $tag = null): ContentTag
    {
        return DB::transaction(function () use ($data, $actor, $tag): ContentTag {
            $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
            $data['updated_by'] = $actor->getAuthIdentifier();
            $data['created_by'] ??= $actor->getAuthIdentifier();
            $saved = $tag ? $this->tags->update($tag, $data) : $this->tags->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($tag ? 'tag.updated' : 'tag.created')->log($tag ? 'Tag updated' : 'Tag created');

            return $saved;
        });
    }

    public function delete(ContentTag $tag, Authenticatable $actor): void
    {
        $this->tags->delete($tag);
        activity('content')->causedBy($actor)->event('tag.deleted')->withProperties(['tag_id' => $tag->id])->log('Tag deleted');
    }
}
