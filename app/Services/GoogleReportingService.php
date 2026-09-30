<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleReportingService
{
    public function analytics(string $start, string $end): array
    {
        $property = (string) config('dashboard.property_id');
        if (! ctype_digit($property)) {
            throw new RuntimeException('Analytics is not configured.');
        }

        $token = $this->token(config('dashboard.analytics_credentials'), 'analytics.readonly');
        $requests = [];
        foreach ([
            [[], ['activeUsers'], 1],
            [['newVsReturning'], ['activeUsers'], 10],
            [['country'], ['screenPageViews'], 15],
            [['deviceCategory'], ['activeUsers'], 10],
            [['pageTitle', 'fullPageUrl'], ['screenPageViews'], 15],
        ] as [$dimensions, $metrics, $limit]) {
            $requests[] = [
                'dateRanges' => [['startDate' => $start, 'endDate' => $end]],
                'dimensions' => array_map(fn ($name) => ['name' => $name], $dimensions),
                'metrics' => array_map(fn ($name) => ['name' => $name], $metrics),
                'limit' => (string) $limit,
                'orderBys' => [['metric' => ['metricName' => $metrics[0]], 'desc' => true]],
            ];
        }

        $response = Http::withToken($token)->connectTimeout(3)->timeout(10)
            ->post("https://analyticsdata.googleapis.com/v1beta/properties/{$property}:batchRunReports", ['requests' => $requests])->throw()->json();
        if (count($response['reports'] ?? []) !== 5) {
            throw new RuntimeException('Incomplete analytics response.');
        }

        $reports = array_map(fn ($report) => $report['rows'] ?? [], $response['reports']);
        $types = collect($reports[1])->mapWithKeys(fn ($row) => [$row['dimensionValues'][0]['value'] => (int) $row['metricValues'][0]['value']]);

        return [
            'active' => (int) ($reports[0][0]['metricValues'][0]['value'] ?? 0),
            'new' => $types->get('new', 0),
            'returning' => $types->get('returning', 0),
            'countries' => $this->series($reports[2]),
            'devices' => $this->series($reports[3]),
            'pages' => array_map(fn ($row) => [
                'title' => $row['dimensionValues'][0]['value'],
                'url' => $this->pageUrl($row['dimensionValues'][1]['value']),
                'views' => (int) $row['metricValues'][0]['value'],
            ], $reports[4]),
        ];
    }

    public function search(string $start, string $end): array
    {
        $site = config('dashboard.site_url');
        if (! is_string($site) || $site === '') {
            throw new RuntimeException('Search Console is not configured.');
        }
        $token = $this->token(config('dashboard.search_credentials'), 'webmasters.readonly');
        $response = Http::withToken($token)->connectTimeout(3)->timeout(10)
            ->post('https://www.googleapis.com/webmasters/v3/sites/'.rawurlencode($site).'/searchAnalytics/query', [
                'startDate' => $start, 'endDate' => $end, 'dimensions' => ['query'],
                'rowLimit' => 15, 'startRow' => 0, 'dataState' => 'final', 'type' => 'web',
            ])->throw()->json();

        return array_map(fn ($row) => [
            'query' => $row['keys'][0] ?? '',
            'clicks' => (int) ($row['clicks'] ?? 0),
            'impressions' => (int) ($row['impressions'] ?? 0),
            'ctr' => round(($row['ctr'] ?? 0) * 100, 2),
            'position' => round($row['position'] ?? 0, 2),
        ], $response['rows'] ?? []);
    }

    private function series(array $rows): array
    {
        return array_map(fn ($row) => ['label' => $row['dimensionValues'][0]['value'], 'value' => (int) $row['metricValues'][0]['value']], $rows);
    }

    private function pageUrl(string $url): ?string
    {
        $url = preg_match('#^https?://#i', $url) ? $url : 'https://'.$url;

        return filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME)), ['https', 'http'], true) ? $url : null;
    }

    private function token(string $path, string $scope): string
    {
        if (! is_readable($path)) {
            throw new RuntimeException('Credentials are unavailable.');
        }
        $credentials = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException('Invalid credentials.');
        }
        $key = 'dashboard-google-token:'.hash('sha256', $credentials['private_key'].$scope);

        return Cache::remember($key, 3000, function () use ($credentials, $scope) {
            $encode = fn ($value) => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
            $unsigned = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])).'.'.$encode(json_encode([
                'iss' => $credentials['client_email'], 'scope' => 'https://www.googleapis.com/auth/'.$scope,
                'aud' => 'https://oauth2.googleapis.com/token', 'iat' => time(), 'exp' => time() + 3600,
            ]));
            if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Unable to authenticate.');
            }
            $response = Http::asForm()->connectTimeout(3)->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned.'.'.$encode($signature),
            ])->throw()->json();
            if (empty($response['access_token'])) {
                throw new RuntimeException('Authentication failed.');
            }

            return $response['access_token'];
        });
    }
}
