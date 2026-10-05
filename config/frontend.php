<?php

return [
    'assets' => [
        'entrypoints' => ['resources/front/css/app.css', 'resources/front/js/app.js'],
        'build_directory' => 'build/front',
        'hot_file' => 'framework/vite-front.hot',
    ],
    'layout' => [
        'header' => 'front.partials.header',
        'footer' => 'front.partials.footer',
    ],
    'branding' => [
        'logo' => 'front/images/svg/nepal-emblem.svg',
        'footer_illustration' => 'front/images/svg/footer.svg',
    ],
    'defaults' => [
        'province_name' => 'Lumbini Province',
        'training_url' => 'https://tmis.pcgg.lumbini.gov.np/routines?status=all',
        'tmis_url' => 'https://tmis.pcgg.lumbini.gov.np/',
        'footer_text' => 'All Rights Reserved.',
        'office_hours' => "Summer (Magh 16–Kartik 15): Sun–Fri, 9:00 AM–5:00 PM\nWinter (Kartik 16–Magh 15): Sun–Fri, 9:00 AM–4:00 PM",
    ],
    'menu_locations' => ['header' => 'header', 'footer' => 'footer', 'important_links' => 'important_links'],
    // CMS menus take precedence; these entries are used when no menu exists.
    'navigation' => [
        'header' => [
            ['label' => 'Home', 'route' => 'public.home'],
            ['label' => 'Training', 'setting' => 'training_url', 'external' => true],
            ['label' => 'Organization', 'route' => 'public.team.index', 'children' => [
                ['label' => 'Our Team', 'route' => 'public.team.index'],
                ['label' => 'Legal Documents', 'route' => 'public.resources.index'],
            ]],
            ['label' => 'Notice Board', 'route' => 'public.notices.index', 'children' => [
                ['label' => 'News', 'route' => 'public.news.index'],
                ['label' => 'Notice', 'route' => 'public.notices.index'],
            ]],
            ['label' => 'Downloads', 'route' => 'public.resources.index'],
            ['label' => 'Contact Us', 'route' => 'public.contact'],
        ],
        'footer' => [
            ['label' => 'Home', 'route' => 'public.home'],
            ['label' => 'Our Team', 'route' => 'public.team.index'],
            ['label' => 'Notice Board', 'route' => 'public.notices.index'],
            ['label' => 'Resources', 'route' => 'public.resources.index'],
            ['label' => 'Gallery', 'route' => 'public.gallery.index'],
            ['label' => 'Videos', 'route' => 'public.videos.index'],
            ['label' => 'Our Halls', 'route' => 'public.halls.index'],
            ['label' => 'Contact Us', 'route' => 'public.contact'],
        ],
    ],
    'name' => 'Lumbini Research and Training Institute',
    'hero_title' => 'Building capable and future-ready civil servants',
    'hero_description' => 'Practical learning experiences that strengthen people, institutions, and the communities they serve.',
    'cache_seconds' => 60,
    'sitemap_chunk_size' => 1000,
    'image_widths' => [480, 960, 1600],
    'about_title' => 'Provincial Centre for Good Governance',
    'about_description' => '<p class="text-white/80!">
                            Established in September 2020 under the Province Good Governance Act, 2077, the
                            Provincial Centre for Good Governance (PCGG) supports institutional development
                            and capacity building across provincial and local governments. With support from
                            the Provincial and Local Governance Support Programme (PLGSP), PCGG works to
                            strengthen public institutions and improve the quality of services for citizens.
                        </p><p class="text-white/80!">
                            PCGG’s work includes capacity development for local governments, elected
                            representatives, and civil servants, helping them deliver responsive, quality
                            services in line with Nepal’s Constitution.
                        </p>',
    'homepage_services' => [['title' => 'Capacity Developments', 'icon' => 'capacity.svg', 'description' => 'Building skills, leadership, and practical capacity through focused learning programmes.'], ['title' => 'Organizational Development', 'icon' => 'organization.svg', 'description' => 'Strengthening systems, teams, and institutional performance for lasting impact.'], ['title' => 'Study and Research', 'icon' => 'research.svg', 'description' => 'Generating evidence and insight to support thoughtful decisions and better outcomes.'], ['title' => 'Consultancy Services', 'icon' => 'consultant.svg', 'description' => 'Providing practical expertise tailored to organizational needs and priorities.']],
    'important_links' => [['label' => 'Office of the Chief Minister and Council of Ministers, Lumbini Province', 'url' => 'https://ocmcm.lumbini.gov.np'], ['label' => 'Ministry of Energy, Water Resources and Irrigation, Lumbini Province', 'url' => 'https://moewri.lumbini.gov.np'], ['label' => 'Ministry of Economic Affairs and Planning, Lumbini Province', 'url' => 'https://moeap.lumbini.gov.np'], ['label' => 'Ministry of Internal Affairs and Law, Lumbini Province', 'url' => 'https://moial.lumbini.gov.np/en/'], ['label' => 'Ministry of Physical Infrastructure Development, Lumbini Province', 'url' => 'https://mopid.lumbini.gov.np']],
];
