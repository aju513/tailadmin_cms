<?php

namespace App\Services\Frontend;

class VideoEmbedService
{
    public function url(?string $url): ?string
    {
        if (! $url || ! in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?: ''), ['http', 'https'], true)) {
            return null;
        }
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
        $id = null;
        if ($host === 'youtu.be') {
            $id = trim($path, '/');
        }
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            $id = $query['v'] ?? null;
            if (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})/?$~', $path, $match)) {
                $id = $match[1];
            }
        }
        if (is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)) {
            return 'https://www.youtube-nocookie.com/embed/'.$id;
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true) && preg_match('~^/(?:video/)?([0-9]+)/?$~', $path, $match)) {
            return 'https://player.vimeo.com/video/'.$match[1].'?dnt=1';
        }

        return null;
    }
}
