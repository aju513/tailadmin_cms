# Permissions and Menus

## Permission catalog

`config/permissions.php` returns groups of stable dot-separated permission names with administrator-facing metadata. Every permission defines a `view_title` (the display text) and a `description` explaining what access it grants. These fields are synchronized to the permissions table as well. Add a permission to that file before using it in routes, FormRequests, Blade, or navigation.

CRUD features use five independent abilities: `<feature>.manage`, `<feature>.show`, `<feature>.create`, `<feature>.edit`, and `<feature>.delete`. `manage` protects the index/filter page and sidebar entry; the other abilities protect their matching page, route, and action button. Permissions are never inferred from hidden navigation or from another CRUD ability.

For example:

```php
'products' => [
    'products.manage' => [
        'view_title' => 'Manage products',
        'description' => 'Allows access to the product index, filters, and pagination.',
    ],
    'products.show' => [
        'view_title' => 'View products',
        'description' => 'Allows viewing individual product details.',
    ],
    'products.create' => [
        'view_title' => 'Create products',
        'description' => 'Allows creating new products.',
    ],
    'products.edit' => [
        'view_title' => 'Edit products',
        'description' => 'Allows updating product details.',
    ],
    'products.delete' => [
        'view_title' => 'Delete products',
        'description' => 'Allows deleting products.',
    ],
],
```

Run:

```bash
php artisan admin:permissions-sync
```

The command validates duplicate names and all menu references, then performs an exact transaction: missing permissions are inserted and database permissions absent from configuration are deleted. It synchronizes all configured permissions to the protected `super-admin` role. A validation failure leaves the database unchanged.

## Menu catalog

`config/admin-menu.php` defines ordered items with these keys:

The Users Management and System groups are omitted from the sidebar. Their existing named routes and server-side permissions remain available for authorized direct access. To show these groups again, restore their entries in the configuration and regenerate the menu.

```php
    [
        'key' => 'users',
        'label' => 'Users',
        'icon' => 'users',
        'route' => 'admin.users.index',
        'permission' => 'users.manage',
        'order' => 10,
]
```

Top-level entries may contain `children`. Keys must be unique, routes must be named and registered, and every permission must exist in the permission catalog.

Child entries may include `active_routes`, an array of named routes that keep their link highlighted and parent group expanded. Without it, the link's own route determines its active state. News uses explicit index/edit/detail routes for its Manage links so its Add links remain independently highlighted.

Run:

```bash
php artisan admin:menu-regenerate
```

The command validates the definition and atomically replaces `bootstrap/cache/admin-menu.php`. The navigation service filters this compiled manifest at request time with `$user->can(...)` and removes empty groups. Route authorization remains mandatory even when an item is hidden.

## Public website links

Main, Footer, and dynamic menu managers can assign page branches or add a label and safe internal path, external URL or grouping anchor, optionally under an item in the same menu. StoreMenuLinkRequest, MenuService and MenuRepository enforce `menus.manage` and parent membership. Public menus omit unpublished/scheduled pages and descendants of hidden parents.

The three database positions use `menus.location` values `header`, `footer`, and `important_links`. The Important Links manager is available at `/admin/menus/important-links` through the named route `admin.menus.important-links` and the generated Menus sidebar group. It accepts only a label and a full HTTP or HTTPS external URL, without page assignments or parent items. FormRequests and the service reject page assignment, nesting, and internal or malformed URLs; reordering also rejects parent IDs. Drag ordering and single/bulk deletion remain available. Public Important Links form a flat footer list and open in a new tab with `noopener noreferrer`.

Run `php artisan migrate`, `php artisan admin:permissions-sync`, `php artisan admin:menu-regenerate`, and `php artisan frontend:cache-clear` when deploying this addition. The `2026_10_04_110000_add_important_links_menu` migration creates the new position and imports the former `site_settings.important_links` JSON list in its saved order. When that setting is absent/null, it imports `config/frontend.php`'s `important_links` defaults; a saved empty list remains empty. Existing Important Links positions are preserved, including empty menus. Menu URLs use a text column to preserve the URLs of up to 1,000 characters accepted by the existing settings and menu requests; custom menu labels accept up to 255 characters.

Important Links are managed through Menus after migration. The Site Settings editor no longer exposes the list, and settings requests reject attempts to modify it. The original JSON row is retained as a record of the imported links, but public rendering reads the menu. An existing empty menu suppresses configuration defaults. Migration rollback retains the new position, its links, and the expanded URL column to preserve later edits. Model observers invalidate the frontend cache after committed menu changes; reordering explicitly invalidates it after commit.

The `2026_10_04_120000_flatten_important_links_menu` migration removes parent relationships from Important Links and preserves their displayed order, IDs, labels, and URLs. Other menu hierarchies are unchanged. Existing page assignments and internal links remain visible in the admin list for removal, but are excluded from the public Important Links list. Safe configuration fallbacks also accept only full HTTP/HTTPS URLs. Run `php artisan migrate` and `php artisan frontend:cache-clear` when installing this update.

The admin workspace separates the page/link composer from the menu structure. Main, Footer, and dynamic positions use Pages/Custom link tabs; Important Links shows its external-link form directly. Failed link submissions restore the custom-link tab with its validation errors. The structure lists labels and destinations together, indents child items, and keeps selection, ordering, and deletion controls aligned. Desktop panels sit side by side; smaller screens stack them and contain table scrolling within the card. This layout change uses the existing requests, routes, authorization, and persistence workflows.
