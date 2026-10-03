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

The `field` component is the shared wrapper used by the controls for labels, required markers, help text, and validation errors. Add new controls by composing it rather than recreating those concerns.

The protected `/admin/ui-kit` page is the executable catalog. New shared components must include sensible defaults, dark-mode styles, accessible labels/focus states, an example there, and a documentation update here.

Forms use the reusable `x-form.*` controls and always render server validation errors through the authenticated layout or auth form feedback.

Page headers use `<x-common.page-breadcrumb>` for a consistently left-aligned title and breadcrumb. Pages with a header action should pass it through the component's `actions` slot so the action remains aligned on the right at desktop widths and stacks cleanly on small screens.
