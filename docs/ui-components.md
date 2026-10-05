# TailAdmin UI Components

## Layouts

- `admin.layouts.app` is the authenticated shell: responsive sidebar, header, flash/error feedback, dark mode, and content container.
- `admin.layouts.auth` is for login and password-reset screens under `admin.auth`.
- `front.layouts.app` is the public shell with configurable header/footer partials, shared settings/menus and separate frontend assets.
- Page views set a title, extend a layout, and render content through `@section('content')`.

## Reusable components

- `<x-common.page-breadcrumb>` supplies the page heading.
- `<x-common.component-card>` supplies a consistent bordered card with title, description, and slot.
- `<x-ui.button>` supports `primary`, `secondary`, `success`, `danger`, `warning`, `info`, and `outline` variants plus `sm`/`md` sizes. Buttons include focus, hover, and disabled states.
- `<x-ui.badge>` supports status colors and light/solid variants.
- `<x-ui.alert>` supports success, warning, error, and information feedback.
- `<x-common.menu-icon>` renders the named SVG icon used by the generated sidebar menu.
- `<x-common.table-checkbox>` provides a 22px square selection control with a white animated checkmark, partial-selection indicator, keyboard focus ring, dark mode, and reduced-motion support. Pass an accessible `aria-label` and native/Alpine input attributes. Homepage Slides demonstrates page-scoped select-all behavior.
- `<x-common.table-move-controls :label="$record->title" />` supplies a drag handle and accessible up/down buttons inside an index row. Pair it with `recordOrdering(url, canReorder, { label: 'Video' })`, an `x-ref="rows"` tbody, `data-record-id` rows, and the existing drag handlers. Controls respect page edges and pending requests. The UI Kit includes an interactive local demonstration.

## Form components

Admin index tables use `pageManager(statuses, selectionKey)` for shared selection and CSRF-protected AJAX status state. `<x-common.table-select-all>` selects displayed rows and indicates partial selection; `<x-common.table-status>` renders authorized AJAX toggles or read-only indicators with matching 32px targets and filled icons; `<x-common.table-status-feedback>` announces success/errors. Boolean managers pass string values `1`/`0`, while publication and user managers retain their enum values. Keep status maps and selection scope on the same outer Alpine component as bulk actions.

CRUD forms must compose the reusable `x-form.*` components below instead of duplicating input markup:

| Component | Use | Example |
| --- | --- | --- |
| `x-form.input` | Text, email, password, number, and other native inputs | `<x-form.input name="sku" label="SKU" required />` |
| `x-form.select` | A native dropdown with normalized options | `<x-form.select name="status" label="Status" :options="['active' => 'Active']" />` |
| `x-form.searchable-select` | Searchable single-value dropdown | `<x-form.searchable-select name="category_id" label="Category" :options="$categories" />` |
| `x-form.textarea` | Multi-line text | `<x-form.textarea name="description" label="Description" rows="5" />` |
| `x-form.date-picker` | Flatpickr date, range, multiple-date, or time input | `<x-form.date-picker name="published_at" label="Publish date" />` |
| `x-form.multiselect` | Searchable checkbox-style multi-value dropdown | `<x-form.multiselect name="tags[]" label="Tags" :options="$tags" />` |
| `x-form.toggle` | A boolean on/off field | `<x-form.toggle name="is_active" label="Active" :checked="true" />` |
| `x-form.checkbox` | Single or array checkbox values | `<x-form.checkbox name="features[]" value="reports" label="Reports" />` |
| `x-form.file-upload` | Accessible drag-and-drop file input with previews and client-side feedback | `<x-form.file-upload name="images[]" label="Images" accept="image/*" :multiple="true" />` |
| `x-form.editor` | CKEditor rich-text editor with formatting, lists, links, tables, source view, and a custom block quote section action | `<x-form.editor name="body" label="Body" />` |

All components accept `label`, `value`/`checked`, `required`, `disabled`, `help`, and `error` where applicable. They preserve old input, display validation feedback, support dark mode, and accept additional HTML attributes. The editor submits HTML through its backing textarea and must be sanitized server-side. CKEditor assets are vendored under `public/vendor/ckeditor`; this setup does not include a file manager or upload endpoint. File-upload forms must use `enctype="multipart/form-data"`; the component's client-side checks are only a usability aid, so every upload must be validated again in its FormRequest. Existing specialized components such as `x-form.input.radio` and file inputs should also be preferred when their control type is needed.

Admin uploads pass `upload-profile` to `x-form.file-upload`, for example `<x-form.file-upload name="banner_image" label="Banner image" upload-profile="images.page.banner" />`. Profiles in `config/settings.php` supply accepted extensions, the maximum file size and recommended/required pixel dimensions. This profile takes precedence over individual `accept` and `max-size` attributes. The FormRequest uses `UploadProfile::rules('images.page.banner')` for the same server limits; `UploadProfile::rules($profile, 'required')` handles required fields and array entries. See [content-management.md](content-management.md) for all profiles and overrides.

The shared TailAdmin font is Plus Jakarta Sans, including the rich-text editing area.

Content URL suggestions use the shared `slugify` helper in `resources/admin/js/components/slug-editor.js`. Pages, Resources, Gallery, and Halls register `slugEditor(initial)` around their source and URL inputs; News and Notices reuse the same helper through `newsEditor`. Bind the title/name with `x-bind:value="title"` and `@input="updateTitle($event.target.value)"`, and bind the editable URL with `x-bind:value="slug"` and `@input="updateSlug($event.target.value)"`. Initial state includes old input and the record's `existing` flag. New forms suggest URLs while typing, manual values survive subsequent title changes and validation reloads, and existing URLs remain stable until explicitly edited. Clearing a URL resumes suggestions on the next source change. Bilingual Pages and Halls use the English title. Server FormRequests and services still enforce URL validation, uniqueness, blank-field fallback, and Page parent paths. Resource Category forms and index omit the slug from the visible UI; the service generates it from the name on create and preserves it on edit.

The `field` component is the shared wrapper used by the controls for labels, required markers, help text, and validation errors. Add new controls by composing it rather than recreating those concerns.

The protected `/admin/ui-kit` page is the executable catalog. New shared components must include sensible defaults, dark-mode styles, accessible labels/focus states, an example there, and a documentation update here.

Forms use the reusable `x-form.*` controls and always render server validation errors through the authenticated layout or auth form feedback.

Page headers use `<x-common.page-breadcrumb>` for a consistently left-aligned title and breadcrumb. Pages with a header action should pass it through the component's `actions` slot so the action remains aligned on the right at desktop widths and stacks cleanly on small screens.

`x-common.form-actions` supplies paired header and scrolling actions. Give the form a unique ID, render `<x-common.form-actions form-id="news-form" close-route="admin.news.index" close-permission="news.manage" submit-label="Save news" />` in the breadcrumb actions slot, and render the same component with `:sticky="true"` as the form's first visible child. The header Save button receives `{formId}-save`; the Alpine component observes that button and displays the sticky bar at 80px once it scrolls above the header. Scrolling back hides the bar, and observer cleanup runs on component removal. Both buttons submit the named form with native validation. Close follows the named route only when its permission allows it; pass the same `:disabled` value to both instances when saving is unavailable. Labels default to Save and the Close action is optional. The component supports dark mode and keyboard focus. A working demonstration is available in the UI Kit.

News, Notices, Resources, Resource Categories, Gallery, Videos, Team Members, Team Categories, Home Slides, and Capacity Reports use these shared actions on their create and edit forms. Team Members and Team Categories also provide header actions alongside their existing bottom Save controls. Capacity Reports uses repeatable key/value rows with stable input IDs, accessible move/remove buttons, and separate responsive cards for the two contribution groups.

Resource forms place Title and URL in equal desktop columns, with the shared date picker and Published toggle below URL and a full-width Description CKEditor. The index replaces the display-order input with drag handles and accessible up/down buttons. Header actions wrap on small screens, while table overflow stays inside its card. Controls respect permissions, page edges, and pending saves; failures restore row order and announce feedback. Search must be cleared before reordering.

Video, Gallery, and Home Slide forms use spaced details and image cards with Published toggles. Video and Gallery use responsive two-column fields. Video Description spans the full width; Gallery retains live editable URL suggestions and existing photo controls; Home Slides use a full-width Title and current-image preview, with no Subtitle or Link URL fields. Their numeric ordering fields are omitted in favor of the shared index move controls. `record-ordering.js` handles page ordering and failure recovery; `resource-ordering.js` uses the same implementation with Resource-specific row attributes and payload names.

The Homepage editor follows this header pattern with Save/Close actions, Description/Gallery/Social/SEO tabs, optional language tabs, and the same image preview/removal controls as Gallery and Halls. Like Pages, it observes the header Save button and reveals a sticky Save/Close bar below the admin header once that button scrolls above the visible area. Its Alpine component disconnects the observer on removal, restores the panel containing server validation errors, and reveals the panel/language containing a browser-invalid required field. Tab changes dispatch the existing editor resize event. SEO title suggestions preserve manual edits. See [homepage.md](homepage.md) for the feature and upload profiles.

The admin layout applies the saved theme in the document head. Its body class updates use a null guard because the body may not exist yet; the Alpine theme store completes initialization after the body loads.

Public menu managers use a responsive workspace with the item composer on the left and the menu structure on the right at desktop widths. Main, Footer, and dynamic menus provide Pages/Custom link tabs with keyboard navigation; failed custom-link submissions reopen the relevant tab. Important Links presents only its external-link form. The four-column structure table combines each label and destination, uses indented submenu rows, and provides drag handles, selection counts, and accessible delete controls. Panels stack on smaller screens, and table overflow stays inside its card. Menu-specific control IDs avoid collisions when multiple positions appear together. The private `_composer`, `_structure`, `_item-row`, and `_scripts` partials share the same TailAdmin spacing, borders, feedback, empty states, and dark-mode colors.
