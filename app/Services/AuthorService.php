<?php

namespace App\Services;

use App\Models\ContentAuthor;
use App\Repositories\Contracts\ContentRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthorService
{
    public function __construct(private readonly ContentRepositoryInterface $authors) {}

    public function save(array $data, Authenticatable $actor, ?ContentAuthor $author = null): ContentAuthor
    {
        return DB::transaction(function () use ($data, $actor, $author): ContentAuthor {
            $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
            $data['updated_by'] = $actor->getAuthIdentifier();
            $data['created_by'] ??= $actor->getAuthIdentifier();
            $saved = $author ? $this->authors->update($author, $data) : $this->authors->create($data);
            activity('content')->causedBy($actor)->performedOn($saved)->event($author ? 'author.updated' : 'author.created')->log($author ? 'Author updated' : 'Author created');

            return $saved;
        });
    }

    public function delete(ContentAuthor $author, Authenticatable $actor): void
    {
        $this->authors->delete($author);
        activity('content')->causedBy($actor)->event('author.deleted')->withProperties(['author_id' => $author->id])->log('Author deleted');
    }
}
