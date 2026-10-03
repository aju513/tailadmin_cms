<?php

namespace App\Services;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class TeamMemberService
{
    public function __construct(private readonly TeamMemberRepositoryInterface $members, private readonly MediaAssetService $media) {}

    public function save(array $data, Authenticatable $actor, ?TeamMember $member = null): TeamMember
    {
        $photo = Arr::pull($data, 'photo');
        $altText = Arr::pull($data, 'photo_alt_text');
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['updated_by'] = $actor->getAuthIdentifier();
        $data['created_by'] ??= $actor->getAuthIdentifier();
        if (! $member) {
            $data['sort_order'] = $this->members->nextSortOrder();
        }

        $photoAsset = $photo instanceof UploadedFile
            ? $this->media->store($photo, $actor, $data['name'] ?? $member?->name, $altText)
            : null;
        if ($photoAsset) {
            $data['photo_media_id'] = $photoAsset->id;
        }

        try {
            return DB::transaction(function () use ($data, $actor, $member): TeamMember {
                $saved = $member
                    ? $this->members->update($member, $data)
                    : $this->members->create($data);

                activity('content')
                    ->causedBy($actor)
                    ->performedOn($saved)
                    ->event($member ? 'team-member.updated' : 'team-member.created')
                    ->withProperties(['team_member_id' => $saved->id, 'is_active' => $saved->is_active])
                    ->log($member ? 'Team member updated' : 'Team member created');

                return $saved;
            });
        } catch (Throwable $exception) {
            if ($photoAsset) {
                $this->media->delete($photoAsset);
            }

            throw $exception;
        }
    }

    public function delete(TeamMember $member, Authenticatable $actor): void
    {
        DB::transaction(function () use ($member, $actor): void {
            $this->members->delete($member);
            activity('content')->causedBy($actor)->event('team-member.deleted')->withProperties(['team_member_id' => $member->id])->log('Team member deleted');
        });
    }

    public function bulkStatus(array $ids, bool $active): void
    {
        DB::transaction(function () use ($ids, $active): void {
            foreach ($this->members->findByIds($ids) as $member) {
                $this->members->update($member, ['is_active' => $active]);
            }
        });
    }

    public function bulkDelete(array $ids, Authenticatable $actor): void
    {
        DB::transaction(function () use ($ids, $actor): void {
            foreach ($this->members->findByIds($ids) as $member) {
                $this->delete($member, $actor);
            }
        });
    }
}
