<?php

return [
    'nepali' => env('SETTINGS_NEPALI', false),

    'homepage' => ['gallery_limit' => 30],

    // File sizes are in KB. Image profiles inherit these defaults.
    'uploads' => [
        'image' => [
            'type' => 'image',
            'max_size_kb' => 5120,
            'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'],
            'enforce_dimensions' => false,
        ],
        'notice_attachment' => [
            'type' => 'file',
            'max_size_kb' => 10240,
            'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        ],
        'document' => [
            'type' => 'file',
            'max_size_kb' => 10240,
            'mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        ],
    ],

    // Recommended pixel dimensions. Override max_size_kb, mimes or
    // enforce_dimensions in an individual profile when needed.
    'images' => [
        'homepage_slide' => ['width' => 1600, 'height' => 900],
        'homepage' => [
            'gallery' => ['width' => 1200, 'height' => 950, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
            'social' => ['width' => 1200, 'height' => 630, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
        ],
        'page' => [
            'banner' => ['width' => 1400, 'height' => 630],
            'social' => ['width' => 1200, 'height' => 630],
        ],
        'news' => [
            'thumbnail' => ['width' => 600, 'height' => 400],
            'banner' => ['width' => 1400, 'height' => 630],
            'social' => ['width' => 1200, 'height' => 630],
        ],
        'team_member' => ['width' => 600, 'height' => 600],
        'hall' => [
            'thumbnail' => ['width' => 600, 'height' => 450, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
            'banner' => ['width' => 1400, 'height' => 630, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
            'social' => ['width' => 1200, 'height' => 630, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
            'gallery' => ['width' => 1200, 'height' => 900, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
        ],
        'gallery_photo' => ['width' => 1200, 'height' => 900, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
        'video_cover' => ['width' => 1200, 'height' => 675, 'mimes' => ['jpg', 'jpeg', 'png', 'webp']],
        'site_logo' => ['width' => 150, 'height' => 126],
        'contact_officer' => ['width' => 400, 'height' => 400],
    ],
];
