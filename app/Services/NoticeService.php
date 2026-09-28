<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Notice;
use App\Repositories\Contracts\NoticeRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NoticeService
{
    public function __construct(private readonly NoticeRepositoryInterface $notices, private readonly MediaAssetService $media) {}

    public function save(array $data, Authenticatable $actor, ?Notice $notice = null): Notice
    {
        return DB::transaction(function () use ($data, $actor, $notice) {
            $data['slug'] = Str::slug(($data['slug'] ?? null) ?: $data['title']);
            $data['sort_order'] = $data['sort_order'] ?? $this->notices->nextSortOrder();
            if ($this->notices->slugExists($data['slug'], $notice)) {
                throw ValidationException::withMessages(['slug' => 'That URL slug is already in use.']);
            } $status = ContentStatus::from($data['status']);
            if ($status === ContentStatus::Published && ! $actor->can('notices.publish')) {
                throw ValidationException::withMessages(['status' => 'You do not have permission to publish notices.']);
            } if (($data['file'] ?? null) instanceof \Illuminate\Http\UploadedFile) {
                $data['file_media_id'] = $this->media->store($data['file'], $actor, $data['title'], $data['title'])->id;
            } $data = Arr::except($data, ['file']);
            $data['published_at'] = $status === ContentStatus::Published ? ($data['published_at'] ?? $notice?->published_at ?? now()) : null;
            $data['published_by'] = $status === ContentStatus::Published ? ($notice?->published_by ?? $actor->getAuthIdentifier()) : null;
            $data['created_by'] = $notice?->created_by ?? $actor->getAuthIdentifier();
            $data['updated_by'] = $actor->getAuthIdentifier();
            $saved = $notice ? $this->notices->update($notice, $data) : $this->notices->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($notice ? 'notice.updated' : 'notice.created')->withProperties(['notice_id' => $saved->id, 'slug' => $saved->slug])->log($notice ? 'Notice updated' : 'Notice created');

            return $saved;
        });
    }

    public function delete(Notice $notice, Authenticatable $actor): void
    {
        $this->notices->delete($notice);
        activity('content')->causedBy($actor)->performedOn($notice)->event('notice.deleted')->withProperties(['notice_id' => $notice->id])->log('Notice deleted');
    }
}
