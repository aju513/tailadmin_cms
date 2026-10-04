# Hall Management and Booking Implementation Plan

Current scope: manage halls in the admin and display them on the public website. The user has excluded hall booking. Booking phases below are retained as historical proposals and are not scheduled implementation work.

## Website findings and scope

The target is the Lumbini Research and Training Institute website, rather than a travel booking site. Its [hall listing](http://lumbini.ajumaharjan.com.np/hall.php), reviewed on 30 September 2026, describes professional spaces in Nepalgunj, Banke for trainings, workshops, conferences, and official events. It currently shows:

| Hall | Building / Sadan | Capacity | Displayed rental rate |
| --- | --- | --- | --- |
| Prithvi Hall | Dikshya Sadan | 100 seats | NPR 25,000 |
| Annapurna Hall | Sampada Sadan | 40 seats | NPR 25,000 |
| Auditorium Hall | Brihaspati Sadan | 300 seats | NPR 50,000 |

The page does not state the rental unit, inclusions, taxes, payment policy, or cancellation rules. Those values must be confirmed before entering production rates. The linked `/book-hall?select_hall=2175` returned HTTP 404 during inspection; the current booking form and its fields could not be verified. No production hall records or rates are automatically seeded from the website. Amenities are selectable options, not claims about the existing venues.

The travel reference supplies presentation patterns: Add/Manage navigation, media-backed catalogue items, descriptive content, rates, and booking detail tables. Trip departures, passenger add-ons, coupons, package prices in USD, and its soft-delete behavior are not used for halls. The existing CMS page setup supplies TailAdmin styling, sticky save controls, English/Nepali editing, image tabs, rich text, SEO, permissions, and auditing.

### How this fits the broader website CMS

| Website area | CMS direction |
| --- | --- |
| Homepage, About Us, and organizational information | Existing Pages, homepage slides, menus, and site settings supply editorial content and navigation. Add homepage section settings only when integrating each actual section. |
| Our Team | Use the existing Team Members and Team Categories resources. |
| News and Notice Board | Use News and [typed Notices](notices.md). Notices Pages select General Notices, Tender Notices, Press Releases, Application Announcements, or All categories. |
| Legal Documents and Downloads | Use the [Resources catalogue](resources.md) with categories, titles, publication dates, descriptions, attachments, and ordering. Resource Pages select a category or All categories and automatically list published documents. |
| Training | The current navigation links to the external TMIS system. Preserve that configurable external link; do not duplicate its training management or assume access to its data. |
| Online Application Form | Treat submissions and their approval workflow as a future independent module; a page can introduce the form but does not replace submission persistence, validation, and permissions. |
| Book a Venue | Use the new Hall catalogue, followed by the booking and scheduling phases below. |
| Contact and media | Use existing site settings and editorial pages for contact information. Photo albums and URL-based videos now have dedicated admin catalogues under Media; see [Photo gallery and videos](media-catalogues.md). Their public frontend integration remains separate. |

Each operational entity should own its structured data. Pages remain the shared editorial layer for introductions, policies, banners, and SEO. Preserve the public website's design and legacy URLs during frontend integration rather than introducing travel-specific entities into this CMS.

**Implemented in this change:** the admin hall catalogue and media management. **Planned next:** booking requests, date availability, approval, blocked dates, notifications, and public integration. The catalogue does not reserve any dates or process payments.

## Phase 1: Hall catalogue — implemented

### Admin screens and navigation

- Nested **Halls → Add Hall / Manage Halls** between Team and Menus.
- Hall manager: one search field for title/building/location, paginate 15 records, and compact columns for hall, capacity, NPR rate and unit, publication status, and individually authorized View/Edit/Delete actions. Maintenance/unavailable appears beneath status; extra filter dropdowns and creation dates are omitted from the screen. Existing repository filters remain available for future workflows.
- Add/Edit: existing TailAdmin layout and form controls; Save beside the breadcrumb, with the full-width sticky Save/Close bar appearing only after that action scrolls above the header and its buttons aligned to the right. Hall title comes first, followed by essential capacity/rate/availability fields. Description/booking instructions, location/contact details, and SEO sections are expanded initially and animate smoothly when toggled; reduced-motion preferences disable the animation. Language tabs remain available for descriptions when `config('settings.nepali')` is enabled; translated titles sit at the top. Image tabs preserve the existing media setup.
- Details: read-only venue information, media, contacts, amenities, publication details, and safe text previews of rich content.
- Create/Edit validation keeps submitted values and displays field errors. Uploads must be selected again after an invalid submission, as in the existing forms.

### Implemented field catalogue

| Group | Fields | Rules / purpose |
| --- | --- | --- |
| Identity | `title`, `slug`, `building_name` | English title required; optional Nepali title; unique ASCII slug generated from English on creation, retained on edits unless changed; building/Sadan stored separately. |
| Location | `location`, `address`, `map_url` | Optional locality, full address, and HTTP/HTTPS directions link. No embedded map HTML. |
| Capacity | `capacity`, `floor_area` | Positive integer seating capacity required; optional positive floor area in square metres with two decimal places. |
| Content | `summary`, `body`, `booking_instructions` | English and optional Nepali rich text, matching the Pages editor. Empty Nepali fields use English fallback. Existing Nepali content survives edits while bilingual mode is disabled. |
| Rate | `rental_rate`, `rate_unit` | Nullable NPR amount with two decimal places; null means price on request, zero means free; unit required when amount is supplied: hour, half day, day, or event. No assumed tax or deposit calculation. |
| Facilities | `amenities` | Validated, distinct keys from `config/halls.php`: projector, screen, sound system, microphones, Wi-Fi, air conditioning, whiteboard, power backup, parking, wheelchair access, washrooms, drinking water. Additional facilities can be described in the body. |
| Booking contact | `contact_person`, `contact_phone`, `contact_email` | Optional public office/person contact. These are venue contacts, not customers or staff account credentials. |
| Operations | `availability_status` | Available for requests, under maintenance, or unavailable for requests. This does not indicate date-specific calendar availability. |
| Publication | `status`, `published_at` | Draft/published status; optional future publication date. Publishing, unpublishing, and changing a published hall require `halls.publish`. Public enforcement will be introduced with public integration. |
| Ordering | `sort_order` | Non-negative display order; results sort by this, then creation date. No new drag-and-drop workflow in this phase. |
| Media | thumbnail, banner, social image, gallery | Shared media asset relationships; JPG/PNG/WebP, 5 MB per file; up to 12 gallery images. Dedicated image alt text accompanies new primary uploads. Gallery alt text defaults to the English title. Individual gallery removals are scoped to the current hall; new images append in upload order. |
| SEO | `meta_title`, `meta_description` | Shared fields matching Pages. |
| Audit | `created_by`, `updated_by`, `published_by`, timestamps | Server-owned user identifiers; never accepted from submitted input. |

The model uses Spatie translations for `title`, `summary`, `body`, and `booking_instructions`. Other fields are shared across languages. Rates are fixed to NPR in the admin UI; a currency selector is unnecessary for the current Nepal venue catalogue.

### Backend structure

`Route → Hall FormRequest → HallController → HallService → HallRepositoryInterface → HallRepository → Hall / HallGalleryImage`

`HallService` owns create/update/delete transactions, publication permission checks, translation fallback, stable slug generation, media attachment/removal, the gallery size limit, and activity events. Repositories own queries, pagination, eager loading, persistence, row locking, and gallery relationships. Existing hall updates lock the hall row during the transaction to serialize gallery changes. New files are cleaned up if the database workflow rolls back. Removing or replacing attachments preserves uploaded files. Deleting a hall removes its gallery relationships but preserves reusable media assets.

Permissions are code-owned: `halls.manage`, `halls.show`, `halls.create`, `halls.edit`, `halls.delete`, and `halls.publish`. Routes and FormRequests check the matching ability; service mutations also enforce authorization. The repository contract is bound in `AppServiceProvider`. No role names, direct user permissions, soft deletes, public registration, or new authentication systems are introduced.

The existing `PageType::Hall` remains a general editorial page classification. Operational venue records live in `halls`; a page does not become a venue or reserve dates by selecting its Hall type. Connect the editorial landing page to the venue catalogue during public integration rather than duplicating rate/capacity data in pages.

## Phase 2: Booking requests and scheduling — proposed

Use a request-and-approval workflow initially. A website submission creates a pending request, not a confirmed reservation. Pending requests do not block dates by default; approval rechecks availability. This avoids indefinitely reserving a hall for abandoned enquiries. If temporary holds are later required, add an explicit held status, expiry time, and scheduled release workflow.

### Proposed tables and fields

**`hall_bookings`:**

| Group | Proposed fields |
| --- | --- |
| Identity | Internal ID, unique generated booking reference, `hall_id`, source (`website`, `admin`, `phone`), submitted/created timestamps. |
| Applicant | Contact name, organization name, organization type (government/local government/NGO/private/other), email, phone, address. Collect only what the office needs. |
| Event | Event title, purpose, attendee count, start/end datetimes, seating/setup requirements, requested amenities, applicant notes. |
| Rate snapshot | NPR currency, quoted rate, rate unit, number of units, hall subtotal, approved adjustments, final quoted total, quote-valid-until date. Store decimal amounts or integer paisa; never use floating-point money calculations. |
| Workflow | Status (`pending`, `approved`, `rejected`, `cancelled`, `completed`), internal notes, approval/rejection/cancellation reason, action actor IDs and timestamps. |
| Consent / tracking | Consent timestamp and a hashed, scoped tracking token if applicants will view requests without accounts. Keep the token out of activity logs. |

**`hall_blocked_periods`:** hall ID, start/end datetimes, reason, optional internal description, created/updated actor, timestamps. These represent maintenance, internal events, or unavailable periods.

**`hall_booking_status_events`:** booking ID, old/new status, safe reason, actor, timestamp. This preserves the administrative decision history separately from general activity logs.

If reusable time slots are approved, add `hall_time_slots` rather than embedding hard-coded slot labels in controllers. For full-day bookings, normalize a date to the venue's approved opening and closing times. Confirm the business schedule before defining these values.

### Availability and approval rules

1. Store reservation timestamps in UTC and interpret/admin-display them in `Asia/Kathmandu`. Keep the booking timezone explicit, rather than relying on the admin user's browser timezone.
2. Require an existing hall, valid event dates, start before end, positive attendees at or below capacity, and allowed amenities. Public requests require the hall to be published, publication date reached, and operationally available.
3. Confirm lead time, maximum duration, operating hours, holidays, and setup/cleanup buffers with the office. Validate these on the server once configured.
4. Use interval overlap: an existing start is before the proposed end **and** an existing end is after the proposed start. Decide whether back-to-back bookings are allowed; apply setup/cleanup buffers consistently.
5. On approval or rescheduling, lock the hall row and check approved bookings and blocked periods in the same transaction before changing state. All writers, including blocked-period creation, must use the same lock. This prevents two concurrent approvals from reserving the same hall/time.
6. A rejected or cancelled request does not occupy dates. Completed records remain historical reservations for their original intervals. Approved reservations continue occupying dates until explicitly cancelled or rescheduled.
7. Do not alter approved booking dates, hall, attendee count, or quote through an unrestricted generic update. Use a reschedule/requote service that validates and audits the change.
8. Changing hall rates or names must not rewrite historical quotes. Capacity reductions below upcoming approved attendee counts and availability changes affecting upcoming reservations should be rejected or require an explicit resolution workflow.
9. Once bookings exist, make the foreign key to halls restrictive and block hard deletion of referenced halls. Use draft/unavailable status to retire the venue while preserving history. Do not introduce soft deletes without a separate architecture decision.

### Booking admin screens

Extend the existing Halls menu with **Manage Bookings**, **Booking Calendar**, and **Blocked Dates** only when their backend workflows exist. The booking index uses the current table/filter patterns: reference, hall, organization/contact, event dates, attendees, status, submitted date, and authorized actions. The detail page uses cards for applicant details, event, quote, status history, and internal notes. Approve/reject/cancel controls require reasons and separate permissions. Calendar entries link to these detail pages; the calendar is a view over the same booking records, not a second source of availability.

Proposed permissions: `hall-bookings.manage`, `.show`, `.create`, `.edit`, `.approve`, `.reject`, `.cancel`, `.complete`, and `hall-blocks.manage`, `.create`, `.edit`, `.delete`. Add them to the catalog when implementing their corresponding actions.

## Phase 3: Public website integration — proposed

1. Decide final public route names and preserve redirects from the current `/hall.php` and `/book-hall?select_hall=...` links. Old IDs need an explicit mapping to CMS hall records; never assume they match new database IDs.
2. Build the hall listing from published, publication-date-reached hall records. Show venue names, buildings, photos, capacities, NPR rates with units, and an accurate availability label. Under-maintenance venues may remain visible, but cannot accept requests.
3. Provide a hall detail and booking request form with selected hall, event dates/times, attendee count, applicant/organization details, requested facilities, message, and consent. Choose a single-day or multi-day design based on the approved scheduling rules.
4. Expose available slots via a bounded, validated public endpoint. Return availability only; never reveal other applicants' names, contact details, or private event information.
5. Validate requests, apply CSRF protection and rate limiting, and render escaped customer-supplied text. Sanitize administrator rich text before rendering it publicly; the current admin details page shows safe plain-text previews.
6. Show the booking reference and a clear “request received, awaiting approval” confirmation. Do not display a paid or confirmed state merely because a form was submitted.
7. Queue acknowledgement and decision notifications after the transaction commits. Mail configuration and a running queue worker become required only in this phase. Notifications must not include internal notes.
8. Integrate booking contact fallback to site settings, venue listing links into public menus, and the homepage Book a Venue CTA with named routes. Keep the existing frontend's visuals while replacing static data with service-backed records.

## Phase 4: Payments and optional extensions — deferred

Confirm the office's payment process first. If online payments are needed, add payment records, gateway references, reconciliation, verified signed webhooks, and idempotent callbacks as a separate feature. Keep payment status independent from booking approval. A booking request must not charge or imply successful payment by default. Refunds, deposits, tax receipts, organization documents, multi-hall requests, catering, and hall-specific seating layouts need explicit business requirements before implementation.

## Decisions to confirm before booking implementation

- Are displayed rates per hour, half day, day, or event? Are facilities included, and are taxes/deposits applicable?
- Who approves requests, and what response time should applicants expect?
- Do pending requests reserve dates, or does only approval reserve them?
- Are multi-day events allowed? What are opening hours, holidays, lead time, and setup/cleanup buffers?
- Must government organizations provide letters or authorization documents?
- How are cancellations, date changes, free bookings, and internal institute events handled?
- Should venue details and customer communications be bilingual? Which booking fields must be translated?

## Verification and setup

At the user's request, this change does not run tests, builds, Git actions, migrations, or permission/menu synchronization. The following checks remain for a later verification pass: CRUD and independent permissions, publication rules, translated fallback, unique slugs, gallery limits/removal ownership, invalid uploads and rollback cleanup, pagination, and empty states. Before booking release, also verify concurrent approvals, blocked dates, timezones, quote snapshots, privacy, status transitions, notification retries, and rescheduling/cancellation behavior.

Run these setup commands after reviewing the implementation:

```bash
php artisan config:clear
php artisan migrate
php artisan admin:permissions-sync
php artisan admin:menu-regenerate
```

If the public media link does not already exist, run `php artisan storage:link`. Rebuild assets during your usual deployment/build step if the current CSS bundle lacks newly used Tailwind classes. No new Composer or npm dependencies are required. After setup, open `/admin/halls` and create the real venue records with confirmed rates and units. Seeders and factory data are not production hall records.
# Hall manager layout

The Halls index uses the Resources manager header and five-column table. Publish, Unpublish, Add Hall, and Bulk delete appear in the breadcrumb actions slot. Selected hall IDs enable bulk controls; the status icon submits a single hall through the same status route. Status changes require halls.publish; deletion requires halls.delete. Workflows use FormRequests, the Hall service, and the existing bound Hall repository, with transactional writes and activity logging. Thumbnails, building/location, capacity, rental rate, and availability appear in the title cell; View/Edit/Delete and created date share the final cell. Existing reorder icons remain display-only.
