# Capacity Reports

`Reports > Capacity Reports` manages the homepage's **Our Contribution to Capacity Development** section. Each fiscal year has one report containing two ordered lists of key/value pairs: Capacity Development Contribution and Contribution Through Collaboration.

## Editing

- Add a report, choose its fiscal year, and save the two contribution cards. Each card starts with the eight existing metric labels.
- Rename keys, enter figures, and add/remove entries independently in each card. Arrow buttons change their display order. At least one entry must remain in each card.
- Values are non-negative whole numbers. Blank values display `-`; recorded zeros display `0`. Labels are required, trimmed, limited to 160 characters, and unique within their card ignoring case. The same label may appear in both cards.
- Header and scrolling Save report/Close controls follow the shared TailAdmin form pattern. Validation reloads retain submitted rows and their field errors. Fiscal-year filters, edit/delete actions, empty states, and pagination are available on the index.
- Saved reports are live immediately. The homepage lists saved fiscal years from newest to oldest, selects the newest first, and switches both cards together. Labels are escaped and numbers use thousands separators. When no reports exist, the original eight metrics display dashes.

## Configuration

`config/settings.php` owns `fiscal_years`, a list of fiscal-year labels such as `2082/83`. Add new years there as needed; clear Laravel's configuration cache after changing it. Only configured years can be selected for new reports. Imported or previously saved years remain selectable when editing their existing record, even if removed from configuration.

`settings.capacity_reports.metrics` defines the initial eight keys for new reports and the empty homepage. Changing those defaults does not rewrite saved keys. `groups` defines the existing card headings/descriptions; `max_rows` defaults to 40 per card and `max_value` to 1,000,000,000. The editor and server validation share these limits.

## Architecture and permissions

Named `admin.capacity-reports.*` routes use FormRequests, `CapacityReportController`, `CapacityReportService`, the bound `CapacityReportRepositoryInterface`, its Eloquent implementation, and `CapacityReport`. Controllers do not query or mutate models. The model stores ordered JSON lists in `development` and `collaboration`; a unique database constraint protects `fiscal_year`. Create/update/delete workflows use transactions and record `capacity-report.created`, `.updated`, and `.deleted` activities with the fiscal year and actor. A failed write rolls back figures and audit entries. Cache invalidation follows successful writes.

Code-owned `capacity-reports.manage`, `.create`, `.edit`, and `.delete` permissions protect the index, forms, and mutations on the server. The menu comes from `config/admin-menu.php`. Saving immediately updates public figures, so grant write permissions to staff allowed to change live homepage statistics.

## Existing data and setup

The migration imports each legacy Site Settings reporting year into the new table, converting its eight fixed metric keys into labelled rows. The original `capacity_reports` setting remains intact as an archive. General Settings links to the dedicated manager and rejects report writes; deleting all new reports does not restore archived figures.

```sh
php artisan migrate
php artisan admin:permissions-sync
php artisan admin:menu-regenerate
php artisan frontend:cache-clear
npm run build
```

No sample statistics are inserted. Feature tests cover permissions, validation, fiscal-year uniqueness/configuration, editable rows, legacy import, filtering, cache refresh, escaping, deletion, and transaction rollback. JavaScript tests cover row identity, movement, limits, removal, and focus behavior.
