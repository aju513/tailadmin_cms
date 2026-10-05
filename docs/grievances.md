# Grievance pages

Create a page in **Pages → Manage Pages**, choose **Grievance Form** as its page type, and publish it. Its normal CMS URL displays the website-themed form without a sidebar. Optional page summary and body appear above the form. Add the page to a public menu using the existing menu manager.

The form follows the reference field structure: optional full name, email, phone, subject and attachment; required grievance message. Anonymous submissions are supported. Uploads accept PDF, JPG/JPEG, PNG and Word documents, up to 5 MB. Server validation enforces file content type and extension. Message length is limited to 10,000 characters.

## reCAPTCHA v3

Enter the **site key** and **secret key** in **Site Settings → reCAPTCHA v3**. Use score-based v3 keys registered for the live website domain. Set `APP_URL` to that same live origin: the server checks Google's verified hostname against its hostname. Local testing requires keys allowing the local hostname.

The secret is encrypted in `site_settings`. The password field is always blank; leaving it blank preserves the existing secret. A replacement secret overwrites it. Clearing the site key disables submissions. Secrets are excluded from public settings, HTML, validation old input and activity logs. Keep `APP_KEY` stable so saved encrypted keys remain readable.

The page requires JavaScript. It loads Google's script only when an enabled grievance form is submitted and obtains a fresh token with action `grievance_submit`. The server verifies success, action, hostname, timestamp and score before any upload or database write. The score threshold defaults to 0.5 and is configured in `config/settings.php`; verification has a five-second timeout. Missing keys, unreadable secrets, rejected tokens and unavailable Google verification all fail closed. CSRF protection and three POST attempts per minute apply.

See Google's [v3 integration guide](https://developers.google.com/recaptcha/docs/v3) and [server verification guide](https://developers.google.com/recaptcha/docs/verify).

## Storage and admin access

`POST /grievances/{pageId}` (`public.grievances.store`) accepts submissions only for published Grievance Form pages whose publication date is not in the future. The FormRequest validates input; `GrievanceService` verifies reCAPTCHA, locks/rechecks the page, stores the attachment, creates the record and writes a minimal audit entry in a transaction. A failed transaction removes its uploaded file. Submission tokens are neither stored nor flashed.

The `grievances` table stores a unique receipt reference, identity fields, subject, message, attachment metadata and a source-page snapshot. Deleting a page clears its foreign key while preserving submitted records and their original page title/path.

**Grievances** in the admin sidebar provides search and pagination. `grievances.manage` permits listing; `grievances.show` permits viewing individual details and downloading attachments. Assign these permissions through roles. Attachments are stored on the private `local` disk under `grievances/`, outside public media. Only the authorized admin download endpoint serves them, with attachment disposition and no-store headers. There are no public listing or download endpoints.

## Deployment and verification

Run `php artisan migrate`, `php artisan admin:permissions-sync`, `php artisan admin:menu-regenerate` and `npm run build` during deployment. Configure the keys, publish a Grievance Form page and submit a real browser request on the registered domain to verify the live integration.

`tests/Feature/GrievanceTest.php` covers page rendering, page-type creation, public validation, reCAPTCHA rejection and transport failures, anonymous submission, private uploads, transaction rollback, secret handling, pagination, role permissions and protected downloads. Google responses are faked in automated tests.
