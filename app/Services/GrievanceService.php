<?php

namespace App\Services;

use App\Models\Grievance;
use App\Repositories\Contracts\GrievanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GrievanceService
{
    public function __construct(private readonly GrievanceRepositoryInterface $grievances, private readonly RecaptchaService $recaptcha) {}

    public function submit(int $pageId, array $data): Grievance
    {
        $this->recaptcha->verify($data['recaptcha_token']);
        $path = null;
        try {
            return DB::transaction(function () use ($pageId, $data, &$path): Grievance {
                $page = $this->grievances->publishedPage($pageId, true);
                $attachment = $data['attachment'] ?? null;
                if ($attachment instanceof UploadedFile) {
                    $path = $attachment->store('grievances', 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to store grievance attachment.');
                    }
                }
                $grievance = $this->grievances->create([
                    'reference' => 'GRV-'.Str::ulid(), 'page_id' => $page->id, 'page_path' => $page->path, 'page_title' => $page->title,
                    'full_name' => $data['full_name'] ?? null, 'email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null,
                    'subject' => $data['subject'] ?? null, 'message' => $data['message'],
                    'attachment_path' => $path, 'attachment_name' => $attachment?->getClientOriginalName(),
                    'attachment_mime' => $attachment?->getMimeType(), 'attachment_size' => $attachment?->getSize(),
                ]);
                activity('content')->performedOn($grievance)->event('grievance.submitted')
                    ->withProperties(['reference' => $grievance->reference, 'page_id' => $page->id])->log('Grievance submitted');

                return $grievance;
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        Gate::authorize('grievances.manage');

        return $this->grievances->paginate($filters);
    }

    public function find(int $id): Grievance
    {
        Gate::authorize('grievances.show');

        return $this->grievances->find($id);
    }

    public function download(int $id): StreamedResponse
    {
        $grievance = $this->find($id);
        abort_unless($grievance->attachment_path && Storage::disk('local')->exists($grievance->attachment_path), 404);

        return Storage::disk('local')->download($grievance->attachment_path, $grievance->attachment_name, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
