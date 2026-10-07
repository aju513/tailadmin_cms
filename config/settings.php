<?php

return [
    'nepali' => env('SETTINGS_NEPALI', false),

    'recaptcha' => ['minimum_score' => 0.5, 'timeout_seconds' => 5],

    'homepage' => ['gallery_limit' => 30],

    // Fiscal-year choices shared by the Capacity Reports editor and its filters.
    'fiscal_years' => ['2083/84', '2082/83', '2081/82', '2080/81', '2079/80', '2078/79', '2077/78', '2076/77'],

    'capacity_reports' => [
        'max_rows' => 40,
        'max_value' => 1000000000,
        'groups' => [
            'development' => ['title' => 'Capacity Development Contribution', 'description' => 'Key figures from training and capacity development programs'],
            'collaboration' => ['title' => 'Contribution Through Collaboration', 'description' => 'Key figures from working with government agencies and local governments'],
        ],
        'metrics' => [
            'training_programs' => 'Total training programs',
            'participants' => 'Total participants',
            'in_service_programs' => 'In-service training programs',
            'in_service_participants' => 'In-service participants',
            'materials' => 'Training materials developed',
            'dialogues' => 'Issue-focused dialogues',
            'research' => 'Research studies',
            'consultancy' => 'Consultancy services',
        ],
    ],

    // File sizes are in KB. Image profiles inherit these defaults.
    'uploads' => [
        'grievance_attachment' => ['type' => 'file', 'max_size_kb' => 5120, 'mimes' => ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']],
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
        'popup' => ['mimes' => ['jpg', 'jpeg', 'png', 'webp']],
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
