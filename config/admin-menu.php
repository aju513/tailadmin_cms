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
        'route' => 'admin.news.index',
        'permission' => 'news.manage',
        'order' => 22,
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
