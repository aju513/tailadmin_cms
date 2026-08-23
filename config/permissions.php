<?php

return [
    'dashboard' => [
        'dashboard.view' => [
            'view_title' => 'View dashboard',
            'description' => 'Allows access to the main administration dashboard.',
        ],
    ],
    'pages' => [
        'pages.manage' => ['view_title' => 'Manage pages', 'description' => 'Allows access to the page index, filters, and pagination.'],
        'pages.show' => ['view_title' => 'View pages', 'description' => 'Allows viewing page details.'],
        'pages.create' => ['view_title' => 'Create pages', 'description' => 'Allows creating pages.'],
        'pages.edit' => ['view_title' => 'Edit pages', 'description' => 'Allows editing pages.'],
        'pages.delete' => ['view_title' => 'Delete pages', 'description' => 'Allows deleting pages.'],
        'pages.publish' => ['view_title' => 'Publish pages', 'description' => 'Allows publishing and unpublishing pages.'],
    ],
    'content' => [
        'categories.manage' => ['view_title' => 'Manage categories', 'description' => 'Allows managing content categories.'],
        'categories.create' => ['view_title' => 'Create categories', 'description' => 'Allows creating content categories.'],
        'categories.edit' => ['view_title' => 'Edit categories', 'description' => 'Allows editing content categories.'],
        'categories.delete' => ['view_title' => 'Delete categories', 'description' => 'Allows deleting content categories.'],
        'tags.manage' => ['view_title' => 'Manage tags', 'description' => 'Allows managing content tags.'],
        'tags.create' => ['view_title' => 'Create tags', 'description' => 'Allows creating content tags.'],
        'tags.edit' => ['view_title' => 'Edit tags', 'description' => 'Allows editing content tags.'],
        'tags.delete' => ['view_title' => 'Delete tags', 'description' => 'Allows deleting content tags.'],
        'authors.manage' => ['view_title' => 'Manage authors', 'description' => 'Allows managing content authors.'],
        'authors.create' => ['view_title' => 'Create authors', 'description' => 'Allows creating content authors.'],
        'authors.edit' => ['view_title' => 'Edit authors', 'description' => 'Allows editing content authors.'],
        'authors.delete' => ['view_title' => 'Delete authors', 'description' => 'Allows deleting content authors.'],
        'media.manage' => ['view_title' => 'Manage media', 'description' => 'Allows viewing the media library.'],
        'media.create' => ['view_title' => 'Upload media', 'description' => 'Allows uploading media files.'],
        'media.delete' => ['view_title' => 'Delete media', 'description' => 'Allows deleting media files.'],
        'menus.manage' => ['view_title' => 'Manage public menus', 'description' => 'Allows managing public navigation items.'],
        'settings.manage' => ['view_title' => 'Manage site settings', 'description' => 'Allows changing public site settings.'],
        'homepage-slides.manage' => ['view_title' => 'Manage homepage slides', 'description' => 'Allows viewing homepage slides.'],
        'homepage-slides.create' => ['view_title' => 'Create homepage slides', 'description' => 'Allows creating homepage slides.'],
        'homepage-slides.edit' => ['view_title' => 'Edit homepage slides', 'description' => 'Allows editing homepage slides.'],
        'homepage-slides.delete' => ['view_title' => 'Delete homepage slides', 'description' => 'Allows deleting homepage slides.'],
    ],
    'users' => [
        'users.manage' => [
            'view_title' => 'Manage users',
            'description' => 'Allows access to the users index, filters, and pagination.',
        ],
        'users.show' => [
            'view_title' => 'View users',
            'description' => 'Allows viewing individual user details.',
        ],
        'users.create' => [
            'view_title' => 'Create users',
            'description' => 'Allows creating new user accounts.',
        ],
        'users.edit' => [
            'view_title' => 'Edit users',
            'description' => 'Allows updating user account details.',
        ],
        'users.delete' => [
            'view_title' => 'Delete users',
            'description' => 'Allows permanently deleting user accounts.',
        ],
        'users.change-status' => [
            'view_title' => 'Change user status',
            'description' => 'Allows activating or deactivating user accounts.',
        ],
        'users.assign-roles' => [
            'view_title' => 'Assign user roles',
            'description' => 'Allows assigning roles while creating or editing users.',
        ],
    ],
    'roles' => [
        'roles.manage' => [
            'view_title' => 'Manage roles',
            'description' => 'Allows access to the roles index, filters, and pagination.',
        ],
        'roles.show' => [
            'view_title' => 'View roles',
            'description' => 'Allows viewing role permissions and assigned users.',
        ],
        'roles.create' => [
            'view_title' => 'Create roles',
            'description' => 'Allows creating roles and selecting their permissions.',
        ],
        'roles.edit' => [
            'view_title' => 'Edit roles',
            'description' => 'Allows changing role names and assigned permissions.',
        ],
        'roles.delete' => [
            'view_title' => 'Delete roles',
            'description' => 'Allows deleting roles that have no assigned users.',
        ],
    ],
    'system' => [
        'permissions.view' => [
            'view_title' => 'View permissions',
            'description' => 'Allows viewing the configured permission catalog.',
        ],
        'activity-log.view' => [
            'view_title' => 'View activity log',
            'description' => 'Allows viewing administrative and security activity records.',
        ],
        'ui-kit.view' => [
            'view_title' => 'View UI kit',
            'description' => 'Allows viewing the protected TailAdmin component catalog.',
        ],
    ],
];
