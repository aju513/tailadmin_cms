<?php

return [
    ['key' => 'popups', 'label' => 'Popup Manager', 'icon' => 'pages', 'route' => 'admin.popups.index', 'active_routes' => ['admin.popups.*'], 'permission' => 'popups.manage', 'order' => 28],
    [
        'key' => 'grievances', 'label' => 'Grievances', 'icon' => 'pages',
        'route' => 'admin.grievances.index', 'active_routes' => ['admin.grievances.*'],
        'permission' => 'grievances.manage', 'order' => 26,
    ],
    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'icon' => 'dashboard',
        'route' => 'admin.dashboard',
        'permission' => 'dashboard.view',
        'order' => 10,
    ],
    [
        'key' => 'pages',
        'label' => 'Pages',
        'icon' => 'pages',
        'order' => 20,
        'children' => [
            ['key' => 'homepage', 'label' => 'Homepage', 'icon' => 'pages', 'route' => 'admin.homepage.edit', 'permission' => 'homepage.manage', 'order' => 10],
            ['key' => 'pages-manage', 'label' => 'Manage Pages', 'icon' => 'pages', 'route' => 'admin.pages.index', 'active_routes' => ['admin.pages.*'], 'permission' => 'pages.manage', 'order' => 20],
        ],
    ],
    [
        'key' => 'reports',
        'label' => 'Reports',
        'icon' => 'dashboard',
        'order' => 21,
        'children' => [
            ['key' => 'capacity-reports', 'label' => 'Capacity Reports', 'icon' => 'pages', 'route' => 'admin.capacity-reports.index', 'active_routes' => ['admin.capacity-reports.*'], 'permission' => 'capacity-reports.manage', 'order' => 10],
        ],
    ],
    [
        'key' => 'news',
        'label' => 'News',
        'icon' => 'pages',
        'order' => 22,
        'children' => [
            ['key' => 'news-create', 'label' => 'Add News', 'icon' => 'create', 'route' => 'admin.news.create', 'permission' => 'news.create', 'order' => 10],
            ['key' => 'news-manage', 'label' => 'Manage News', 'icon' => 'pages', 'route' => 'admin.news.index', 'active_routes' => ['admin.news.index', 'admin.news.show', 'admin.news.edit'], 'permission' => 'news.manage', 'order' => 20],
        ],
    ],
    [
        'key' => 'notices',
        'label' => 'Notices',
        'icon' => 'pages',
        'order' => 23,
        'children' => [
            ['key' => 'notice-create', 'label' => 'Add Notice', 'icon' => 'create', 'route' => 'admin.notices.create', 'permission' => 'notices.create', 'order' => 10],
            ['key' => 'notices-manage', 'label' => 'Manage Notices', 'icon' => 'pages', 'route' => 'admin.notices.index', 'active_routes' => ['admin.notices.index', 'admin.notices.edit'], 'permission' => 'notices.manage', 'order' => 20],
        ],
    ],
    [
        'key' => 'resources',
        'label' => 'Resources',
        'icon' => 'pages',
        'order' => 24,
        'children' => [
            ['key' => 'resources-create', 'label' => 'Add Resource', 'icon' => 'create', 'route' => 'admin.resources.create', 'permission' => 'resources.create', 'order' => 10],
            ['key' => 'resources-manage', 'label' => 'Manage Resources', 'icon' => 'pages', 'route' => 'admin.resources.index', 'active_routes' => ['admin.resources.index', 'admin.resources.edit'], 'permission' => 'resources.manage', 'order' => 20],
            ['key' => 'resource-categories-create', 'label' => 'Add Resource Category', 'icon' => 'create', 'route' => 'admin.resource-categories.create', 'permission' => 'resource-categories.create', 'order' => 30],
            ['key' => 'resource-categories-manage', 'label' => 'Manage Resource Categories', 'icon' => 'categories', 'route' => 'admin.resource-categories.index', 'active_routes' => ['admin.resource-categories.index', 'admin.resource-categories.edit'], 'permission' => 'resource-categories.manage', 'order' => 40],
        ],
    ],
    [
        'key' => 'team',
        'label' => 'Team',
        'icon' => 'users',
        'order' => 25,
        'children' => [
            ['key' => 'team-create', 'label' => 'Add Team', 'icon' => 'create', 'route' => 'admin.team-members.create', 'permission' => 'team-members.create', 'order' => 10],
            ['key' => 'team-manage', 'label' => 'Manage Team', 'icon' => 'users', 'route' => 'admin.team-members.index', 'active_routes' => ['admin.team-members.index', 'admin.team-members.edit'], 'permission' => 'team-members.manage', 'order' => 20],
            ['key' => 'team-category-create', 'label' => 'Add Team Category', 'icon' => 'create', 'route' => 'admin.team-categories.create', 'permission' => 'team-categories.create', 'order' => 30],
            ['key' => 'team-category-manage', 'label' => 'Manage Team Category', 'icon' => 'pages', 'route' => 'admin.team-categories.index', 'active_routes' => ['admin.team-categories.index', 'admin.team-categories.edit'], 'permission' => 'team-categories.manage', 'order' => 40],
        ],
    ],
    [
        'key' => 'halls',
        'label' => 'Halls',
        'icon' => 'hall',
        'order' => 27,
        'children' => [
            ['key' => 'hall-create', 'label' => 'Add Hall', 'icon' => 'create', 'route' => 'admin.halls.create', 'permission' => 'halls.create', 'order' => 10],
            ['key' => 'halls-manage', 'label' => 'Manage Halls', 'icon' => 'hall', 'route' => 'admin.halls.index', 'active_routes' => ['admin.halls.index', 'admin.halls.edit', 'admin.halls.show'], 'permission' => 'halls.manage', 'order' => 20],
        ],
    ],
    [
        'key' => 'menus',
        'label' => 'Menus',
        'icon' => 'menus',
        'order' => 30,
        'children' => [
            ['key' => 'header-menu', 'label' => 'Main Menu', 'icon' => 'menus', 'route' => 'admin.menus.header', 'permission' => 'menus.manage', 'order' => 10],
            ['key' => 'footer-menu', 'label' => 'Footer Menu', 'icon' => 'menus', 'route' => 'admin.menus.footer', 'permission' => 'menus.manage', 'order' => 20],
            ['key' => 'important-links-menu', 'label' => 'Important Links Menu', 'icon' => 'menus', 'route' => 'admin.menus.important-links', 'permission' => 'menus.manage', 'order' => 30],
        ],
    ],
    [
        'key' => 'media',
        'label' => 'Media',
        'icon' => 'media',
        'order' => 40,
        'children' => [
            ['key' => 'videos-create', 'label' => 'Add Video', 'icon' => 'create', 'route' => 'admin.videos.create', 'permission' => 'videos.create', 'order' => 10],
            ['key' => 'videos', 'label' => 'Manage Video', 'icon' => 'media', 'route' => 'admin.videos.index', 'active_routes' => ['admin.videos.index', 'admin.videos.edit'], 'permission' => 'videos.manage', 'order' => 20],
            ['key' => 'gallery-create', 'label' => 'Add Gallery', 'icon' => 'create', 'route' => 'admin.gallery.create', 'permission' => 'gallery.create', 'order' => 30],
            ['key' => 'gallery', 'label' => 'Manage Gallery', 'icon' => 'media', 'route' => 'admin.gallery.index', 'active_routes' => ['admin.gallery.index', 'admin.gallery.edit'], 'permission' => 'gallery.manage', 'order' => 40],
            ['key' => 'homepage-slides-create', 'label' => 'Add Home Slide', 'icon' => 'create', 'route' => 'admin.homepage-slides.create', 'permission' => 'homepage-slides.create', 'order' => 50],
            ['key' => 'homepage-slides', 'label' => 'Manage Home Slide', 'icon' => 'slides', 'route' => 'admin.homepage-slides.index', 'active_routes' => ['admin.homepage-slides.index', 'admin.homepage-slides.edit'], 'permission' => 'homepage-slides.manage', 'order' => 60],
        ],
    ],
    [
        'key' => 'site-settings',
        'label' => 'Site Settings',
        'icon' => 'settings',
        'order' => 50,
        'children' => [
            ['key' => 'settings', 'label' => 'General Settings', 'icon' => 'settings', 'route' => 'admin.settings.edit', 'permission' => 'settings.manage', 'order' => 10],
        ],
    ],
];
