<?php

return [
    'property_id' => env('ANALYTICS_PROPERTY_ID'),
    'analytics_credentials' => storage_path('app/analytics/service-account-credentials.json'),
    'site_url' => env('SEARCH_CONSOLE_SITE_URL'),
    'search_credentials' => storage_path('app/analytics/search-console-credentials.json'),
];
