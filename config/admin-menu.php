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
        'key' => 'menus',
        'label' => 'Menus',
        'icon' => 'menus',
        'order' => 30,
        'children' => [
            ['key' => 'header-menu', 'label' => 'Header Menu', 'icon' => 'menus', 'route' => 'admin.menus.header', 'permission' => 'menus.manage', 'order' => 10],
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
    [
        'key' => 'users-management',
        'label' => 'Users Management',
        'icon' => 'users',
        'order' => 60,
        'children' => [
            ['key' => 'users', 'label' => 'Users', 'icon' => 'users', 'route' => 'admin.users.index', 'permission' => 'users.manage', 'order' => 10],
        ],
    ],
    [
        'key' => 'system',
        'label' => 'System',
        'icon' => 'system',
        'order' => 70,
        'children' => [
            ['key' => 'roles', 'label' => 'Roles', 'icon' => 'roles', 'route' => 'admin.roles.index', 'permission' => 'roles.manage', 'order' => 10],
            ['key' => 'permissions', 'label' => 'Permissions', 'icon' => 'permissions', 'route' => 'admin.permissions.index', 'permission' => 'permissions.view', 'order' => 20],
            ['key' => 'activity-log', 'label' => 'Activity Log', 'icon' => 'activity-log', 'route' => 'admin.activity.index', 'permission' => 'activity-log.view', 'order' => 30],
            ['key' => 'ui-kit', 'label' => 'UI Kit', 'icon' => 'ui-kit', 'route' => 'admin.ui-kit', 'permission' => 'ui-kit.view', 'order' => 40],
        ],
    ],
];
