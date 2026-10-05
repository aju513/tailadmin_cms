<?php

return [

    'grievances' => [
        'grievances.manage' => ['view_title' => 'Manage grievances', 'description' => 'Allows searching and listing grievance submissions.'],
        'grievances.show' => ['view_title' => 'View grievances', 'description' => 'Allows viewing grievance details and downloading private attachments.'],
    ],

    'resources' => [
        'resources.manage' => ['view_title' => 'Manage resources', 'description' => 'Allows manage operations for resources.'],
        'resources.create' => ['view_title' => 'Create resources', 'description' => 'Allows create operations for resources.'],
        'resources.edit' => ['view_title' => 'Edit resources', 'description' => 'Allows edit operations for resources.'],
        'resources.delete' => ['view_title' => 'Delete resources', 'description' => 'Allows delete operations for resources.'],
        'resources.publish' => ['view_title' => 'Publish resources', 'description' => 'Allows publish operations for resources.'],
    ],
    'resource-categories' => [
        'resource-categories.manage' => ['view_title' => 'Manage resource categories', 'description' => 'Allows manage operations for resource categories.'],
        'resource-categories.create' => ['view_title' => 'Create resource categories', 'description' => 'Allows create operations for resource categories.'],
        'resource-categories.edit' => ['view_title' => 'Edit resource categories', 'description' => 'Allows edit operations for resource categories.'],
        'resource-categories.delete' => ['view_title' => 'Delete resource categories', 'description' => 'Allows delete operations for resource categories.'],
    ],
    'gallery' => [
        'gallery.manage' => ['view_title' => 'Manage photo albums', 'description' => 'Allows manage operations for photo albums.'],
        'gallery.create' => ['view_title' => 'Create photo albums', 'description' => 'Allows create operations for photo albums.'],
        'gallery.edit' => ['view_title' => 'Edit photo albums', 'description' => 'Allows edit operations for photo albums.'],
        'gallery.delete' => ['view_title' => 'Delete photo albums', 'description' => 'Allows delete operations for photo albums.'],
        'gallery.publish' => ['view_title' => 'Publish photo albums', 'description' => 'Allows publish operations for photo albums.'],
    ],
    'videos' => [
        'videos.manage' => ['view_title' => 'Manage videos', 'description' => 'Allows manage operations for videos.'],
        'videos.create' => ['view_title' => 'Create videos', 'description' => 'Allows create operations for videos.'],
        'videos.edit' => ['view_title' => 'Edit videos', 'description' => 'Allows edit operations for videos.'],
        'videos.delete' => ['view_title' => 'Delete videos', 'description' => 'Allows delete operations for videos.'],
        'videos.publish' => ['view_title' => 'Publish videos', 'description' => 'Allows publish operations for videos.'],
    ],
    'halls' => [
        'halls.manage' => ['view_title' => 'Manage halls', 'description' => 'Allows viewing, filtering, and paginating the hall directory.'],
        'halls.show' => ['view_title' => 'View halls', 'description' => 'Allows viewing individual hall details.'],
        'halls.create' => ['view_title' => 'Create halls', 'description' => 'Allows creating halls and attaching hall images.'],
        'halls.edit' => ['view_title' => 'Edit halls', 'description' => 'Allows editing hall details, rental rates, and operational availability.'],
        'halls.delete' => ['view_title' => 'Delete halls', 'description' => 'Allows deleting halls.'],
        'halls.publish' => ['view_title' => 'Publish halls', 'description' => 'Allows publishing, unpublishing, and editing published halls.'],
    ],
    'dashboard' => [
        'dashboard.view' => [
            'view_title' => 'View dashboard',
            'description' => 'Allows access to the main administration dashboard.',
        ],
    ],
    'homepage' => [
        'homepage.manage' => ['view_title' => 'View homepage editor', 'description' => 'Allows viewing the homepage content, gallery, and SEO editor.'],
        'homepage.edit' => ['view_title' => 'Edit homepage content', 'description' => 'Allows saving live homepage text, gallery images, and SEO details.'],
    ],
    'capacity-reports' => [
        'capacity-reports.manage' => ['view_title' => 'Manage capacity reports', 'description' => 'Allows viewing and filtering fiscal-year contribution reports.'],
        'capacity-reports.create' => ['view_title' => 'Create capacity reports', 'description' => 'Allows adding fiscal-year contribution figures.'],
        'capacity-reports.edit' => ['view_title' => 'Edit capacity reports', 'description' => 'Allows changing contribution keys and values for a fiscal year.'],
        'capacity-reports.delete' => ['view_title' => 'Delete capacity reports', 'description' => 'Allows removing fiscal-year contribution reports.'],
    ],
    'pages' => [
        'pages.manage' => ['view_title' => 'Manage pages', 'description' => 'Allows access to the page index, filters, and pagination.'],
        'pages.show' => ['view_title' => 'View pages', 'description' => 'Allows viewing page details.'],
        'pages.create' => ['view_title' => 'Create pages', 'description' => 'Allows creating pages.'],
        'pages.edit' => ['view_title' => 'Edit pages', 'description' => 'Allows editing pages.'],
        'pages.delete' => ['view_title' => 'Delete pages', 'description' => 'Allows deleting pages.'],
        'pages.publish' => ['view_title' => 'Publish pages', 'description' => 'Allows publishing and unpublishing pages.'],
    ],
    'news' => [
        'news.manage' => ['view_title' => 'Manage news', 'description' => 'Allows viewing and filtering news articles.'],
        'news.show' => ['view_title' => 'View news', 'description' => 'Allows viewing news article details.'],
        'news.create' => ['view_title' => 'Create news', 'description' => 'Allows creating news articles.'],
        'news.edit' => ['view_title' => 'Edit news', 'description' => 'Allows editing news articles.'],
        'news.delete' => ['view_title' => 'Delete news', 'description' => 'Allows deleting news articles.'],
        'news.publish' => ['view_title' => 'Publish news', 'description' => 'Allows publishing news articles.'],
    ],
    'notices' => [
        'notices.manage' => ['view_title' => 'Manage notices', 'description' => 'Allows viewing and filtering notices.'],
        'notices.create' => ['view_title' => 'Create notices', 'description' => 'Allows creating notices.'],
        'notices.edit' => ['view_title' => 'Edit notices', 'description' => 'Allows editing notices.'],
        'notices.delete' => ['view_title' => 'Delete notices', 'description' => 'Allows deleting notices.'],
        'notices.publish' => ['view_title' => 'Publish notices', 'description' => 'Allows publishing, unpublishing, editing, and deleting published notices.'],
    ],
    'content' => [
        'team-members.manage' => ['view_title' => 'Manage team members', 'description' => 'Allows viewing and filtering the team member directory.'],
        'team-members.create' => ['view_title' => 'Create team members', 'description' => 'Allows adding team members.'],
        'team-members.edit' => ['view_title' => 'Edit team members', 'description' => 'Allows changing team member details and status.'],
        'team-members.delete' => ['view_title' => 'Delete team members', 'description' => 'Allows removing team members.'],
        'team-categories.manage' => ['view_title' => 'Manage team categories', 'description' => 'Allows viewing team categories.'],
        'team-categories.create' => ['view_title' => 'Create team categories', 'description' => 'Allows creating team categories.'],
        'team-categories.edit' => ['view_title' => 'Edit team categories', 'description' => 'Allows editing team categories.'],
        'team-categories.delete' => ['view_title' => 'Delete team categories', 'description' => 'Allows deleting team categories.'],
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
