<?php

return [
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
        'route' => 'admin.pages.index',
        'permission' => 'pages.manage',
        'order' => 20,
    ],
    [
        'key' => 'news',
        'label' => 'News',
        'icon' => 'pages',
        'order' => 22,
        'children' => [
            ['key' => 'news-create', 'label' => 'Add News', 'icon' => 'create', 'route' => 'admin.news.create', 'permission' => 'news.create', 'order' => 10],
            ['key' => 'news-manage', 'label' => 'Manage News', 'icon' => 'pages', 'route' => 'admin.news.index', 'active_routes' => ['admin.news.index', 'admin.news.show', 'admin.news.edit'], 'permission' => 'news.manage', 'order' => 20],
            ['key' => 'news-category-create', 'label' => 'Add News Category', 'icon' => 'create', 'route' => 'admin.categories.create', 'permission' => 'categories.create', 'order' => 30],
            ['key' => 'news-categories', 'label' => 'Manage News Categories', 'icon' => 'categories', 'route' => 'admin.categories.index', 'active_routes' => ['admin.categories.index', 'admin.categories.edit'], 'permission' => 'categories.manage', 'order' => 40],
            ['key' => 'news-tag-create', 'label' => 'Add News Tag', 'icon' => 'create', 'route' => 'admin.tags.create', 'permission' => 'tags.create', 'order' => 50],
            ['key' => 'news-tags', 'label' => 'Manage News Tags', 'icon' => 'tags', 'route' => 'admin.tags.index', 'active_routes' => ['admin.tags.index', 'admin.tags.edit'], 'permission' => 'tags.manage', 'order' => 60],
            ['key' => 'news-author-create', 'label' => 'Add News Author', 'icon' => 'create', 'route' => 'admin.authors.create', 'permission' => 'authors.create', 'order' => 70],
            ['key' => 'news-authors', 'label' => 'Manage News Authors', 'icon' => 'authors', 'route' => 'admin.authors.index', 'active_routes' => ['admin.authors.index', 'admin.authors.edit'], 'permission' => 'authors.manage', 'order' => 80],
        ],
    ],
    [
        'key' => 'notices',
        'label' => 'Notices',
        'icon' => 'pages',
        'route' => 'admin.notices.index',
        'permission' => 'notices.manage',
        'order' => 23,
    ],
    [
        'key' => 'team',
        'label' => 'Team',
        'icon' => 'users',
        'order' => 25,
        'children' => [
            ['key' => 'team-members', 'label' => 'Team Members', 'icon' => 'users', 'route' => 'admin.team-members.index', 'permission' => 'team-members.manage', 'order' => 10],
            ['key' => 'team-categories', 'label' => 'Team Categories', 'icon' => 'pages', 'route' => 'admin.team-categories.index', 'permission' => 'team-categories.manage', 'order' => 20],
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
        ],
    ],
    [
        'key' => 'media',
        'label' => 'Media',
        'icon' => 'media',
        'order' => 40,
        'children' => [
            ['key' => 'homepage-slides', 'label' => 'Home Slides', 'icon' => 'slides', 'route' => 'admin.homepage-slides.index', 'permission' => 'homepage-slides.manage', 'order' => 10],
            ['key' => 'videos', 'label' => 'Videos', 'icon' => 'media', 'route' => 'admin.media.index', 'permission' => 'media.manage', 'order' => 20],
            ['key' => 'gallery', 'label' => 'Gallery', 'icon' => 'media', 'route' => 'admin.media.index', 'permission' => 'media.manage', 'order' => 30],
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
