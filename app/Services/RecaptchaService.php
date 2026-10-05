<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RecaptchaService
{
    public const ACTION = 'grievance_submit';

    public function __construct(private readonly SiteSettingService $settings) {}

    public function verify(string $token): void
    {
        $keys = $this->settings->recaptcha();
        $valid = false;
        if (filled($keys['site_key']) && filled($keys['secret_key'])) {
            try {
                $response = Http::asForm()->timeout(config('settings.recaptcha.timeout_seconds', 5))
                    ->post('https://www.google.com/recaptcha/api/siteverify', ['secret' => $keys['secret_key'], 'response' => $token]);
                $result = $response->json();
                $hostname = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
                $valid = $response->successful() && is_array($result)
                    && ($result['success'] ?? false) === true
                    && ($result['action'] ?? null) === self::ACTION
                    && $hostname !== '' && is_string($result['hostname'] ?? null) && strtolower($result['hostname']) === $hostname
                    && is_numeric($result['score'] ?? null)
                    && $result['score'] >= config('settings.recaptcha.minimum_score', 0.5)
                    && $result['score'] <= 1
                    && is_string($result['challenge_ts'] ?? null)
                    && preg_match('/^\d{4}-\d{2}-\d{2}T/', $result['challenge_ts']) === 1
                    && CarbonImmutable::parse($result['challenge_ts'])->between(now()->subSeconds(120), now()->addSeconds(30));
            } catch (ConnectionException|\InvalidArgumentException $exception) {
                $valid = false;
            }
        }
        if (! $valid) {
            throw ValidationException::withMessages(['recaptcha_token' => 'Security verification failed. Please reload the page and try again.']);
        }
    }
}
