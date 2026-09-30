<?php

namespace App\Services\Frontend;

use App\Mail\FrontendContactMessage;
use App\Repositories\Contracts\SiteSettingRepositoryInterface;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContactService
{
    public function __construct(private readonly SiteSettingRepositoryInterface $settings) {}

    public function send(array $data): bool
    {
        $recipient = $this->settings->allKeyed()['email'] ?? null;
        if (! $recipient || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            Mail::to($recipient)->send(new FrontendContactMessage($data));

            return true;
        } catch (TransportExceptionInterface) {
            return false;
        }
    }
}
