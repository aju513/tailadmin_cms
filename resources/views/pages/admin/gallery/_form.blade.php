<x-common.component-card title="Album details">
    <div class="grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2"><x-form.input name="title" label="Title" :value="$record->title" required maxlength="255" /></div>
        <x-form.input name="slug" label="URL slug" :value="$record->slug" help="Leave blank to generate from the title. Existing slugs stay unchanged." />
        <x-form.date-picker name="event_date" label="Event date" :value="$record->event_date?->format('Y-m-d')" />
        <x-form.input name="sort_order" label="Display order" type="number" :value="$record->sort_order ?? 0" min="0" required help="Lower numbers appear first." />
        <div class="sm:col-span-2"><x-form.textarea name="description" label="Description" :value="$record->description" rows="4" maxlength="10000" /></div>
    </div>
</x-common.component-card>
<x-common.component-card title="Cover image">
    @if($record->coverMedia)
        <div class="mb-5 flex items-center gap-5">
            <img src="{{ $record->coverMedia->url() }}" alt="{{ $record->title }}" class="h-24 w-36 rounded-lg object-cover" />
            <x-form.checkbox name="remove_cover" value="1" label="Remove current image" :checked="(bool) old('remove_cover', false)" />
        </div>
    @endif
    <x-form.file-upload name="cover" label="Cover image" accept="image/jpeg,image/png,image/webp" :maxSize="5 * 1024 * 1024" help="JPG, PNG, or WebP. Maximum 5 MB. Uploading replaces the current image." />
</x-common.component-card>
@include('pages.admin.gallery._photos')
<x-common.component-card title="Publication">
    <div class="grid gap-6 sm:grid-cols-2">
        @can('gallery.publish')
            <x-form.select name="status" label="Status" :options="['draft' => 'Draft', 'published' => 'Published']" :value="$record->status?->value ?? 'draft'" required />
            <x-form.input name="published_at" label="Publish date and time" type="datetime-local" :value="$record->published_at?->format('Y-m-d\TH:i')" help="Leave blank to publish immediately. Uses the application timezone." />
        @else
            <input type="hidden" name="status" value="{{ $record->status?->value ?? 'draft' }}" />
            <p class="text-sm text-gray-500 dark:text-gray-400">Publication status: {{ ucfirst($record->status?->value ?? 'draft') }}. Publishing permission is required to change published content.</p>
        @endcan
    </div>
</x-common.component-card>
