# Notice Sections (admin)

## Creating sections and assigning notices

Use Pages to create a parent Notice Board and child sections such as Tenders or Vacancies. Set Page Type to Notices for every selectable section, and use Parent Page to define its hierarchy. No separate Notice Category administration is needed.

Add/Edit Notice has a required Notice Section selector. Both the board and its children are selectable, hierarchical labels include the page path, and draft pages are labelled Unpublished. Notice editors do not need Pages management permissions to choose a section. If no sections exist, saving is disabled and the form explains how to create a Notices page; the creation link requires pages.create. Each notice belongs to one section, stored as notices.notice_page_id.

Manage Notices shows the assigned section or Unassigned below the title. Its section filter includes notices assigned directly to that page and its descendants. The existing status, AJAX bulk controls, ordering, attachment, deadline, SEO, and publication permissions remain unchanged. Deadlines label expired notices; they do not unpublish them.

## Existing data and public compatibility

This is an admin-only rollout. Existing notices have no section assigned until an editor chooses one; no sections or mappings are generated automatically. Their legacy notice_category_id is preserved when editing, including inactive-category visibility restrictions.

New notices retain an internal category so unchanged public queries can discover them: use the active General Notices category (slug general), otherwise create/reuse an active compatibility category named General Notices with slug notice-sections-default. Previously inactive categories are not reactivated. Notice category input is no longer accepted from admin forms.

Public URLs, category filters, legacy Page/category connections, publication scheduling, detail visibility, templates, and public listing queries remain unchanged. Section-based public tables and parent/child aggregation are a separate frontend phase; assigning a section does not yet change public placement.

Notice Category admin routes, forms, sidebar links, and management permissions are retired. The underlying category model/table, legacy repository, and read-only options service remain for public compatibility. No notice or category content is deleted.

## Section and menu safeguards

A section with assigned notices cannot change its page type. A page cannot be deleted while it or its descendant sections have assigned notices; move those notices first. The migration uses an indexed nullable foreign key with restricted deletion, and service-level checks give actionable errors.

Selecting any parent page in menu assignment automatically selects and assigns all its children and deeper descendants, including unpublished pages (which remain hidden publicly). Removing a selected parent clears its branch; removing a child also clears selected ancestors so they do not re-add it on save. This only changes the pending selection, not already assigned items. Reassign a parent to include children added since its previous assignment.

When assigning a child Notices page, its Notices-page ancestors are additionally assigned once, without expanding those implicit ancestors to include siblings. The existing page hierarchy nests the child underneath its nearest assigned ancestor. A required menu parent cannot be removed while its child Notice Sections remain; select the children too for bulk removal. Manual links retain their current behavior.

## Architecture and operations

FormRequests validate section IDs against Page Type Notices. Controllers call NoticeService; services own authorization, transactions, assignment checks and activity logging, while repositories own options, filtering, locks, compatibility-category persistence and menu queries. The selected page is rechecked under a row lock during saving. Public category-based reads are not migrated here.

Existing notice create/edit/delete/publish permissions and menus.manage are reused; no new permission is needed. Existing Pages permissions govern creating and editing section pages.

Deploy with:

```bash
php artisan migrate
php artisan admin:permissions-sync
php artisan admin:menu-regenerate
```

Permission synchronization removes retired category permissions and their grants. Back up the database before deployment; the migration leaves all existing notice/category data intact. Rolling back the migration removes only the new section assignment column; preserve those assignments separately if rollback is required.

Feature tests cover parent/child options, direct board assignment, unpublished sections, empty states, invalid assignments, authorization, legacy reassignment, active compatibility categories, section filters, section lifecycle guards, automatic menu ancestors, duplicates and parent-removal safeguards. Run php artisan test, vendor/bin/pint --test, npm run build and the existing JavaScript tests before handoff.
