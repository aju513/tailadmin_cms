<x-common.component-card title="Resource details">
    <div class="grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2"><x-form.input name="title" label="Title" :value="$record->title" required maxlength="255" /></div>
        <x-form.select name="resource_category_id" label="Resource category" :options="$categories" :value="$record->resource_category_id" required />
        <x-form.input name="slug" label="URL slug" :value="$record->slug" help="Leave blank to generate from the title. Existing slugs stay unchanged." />
        <x-form.input name="sort_order" label="Display order" type="number" :value="$record->sort_order ?? 0" min="0" required />
        <div class="sm:col-span-2"><x-form.textarea name="description" label="Description" :value="$record->description" rows="5" maxlength="10000" /></div>
    </div>
    @if(empty($categories))
        <p class="mt-4 text-sm text-gray-500">Create a resource category before adding a document.</p>
        @can('resource-categories.create')<a href="{{ route('admin.resource-categories.create') }}" class="mt-2 inline-block text-sm font-medium text-brand-500">Add resource category</a>@endcan
    @endif
</x-common.component-card>
<x-common.component-card title="Document attachment">
    @if($record->fileMedia)
        <a href="{{ $record->fileMedia->url() }}" target="_blank" rel="noopener noreferrer" class="mb-4 inline-block text-sm font-medium text-brand-500">{{ $record->fileMedia->original_name }}</a>
    @endif
    <x-form.file-upload name="attachment" label="Attachment" upload-profile="uploads.document" :required="! $record->file_media_id" help="Upload a new file to replace the existing attachment." />
</x-common.component-card>
<x-common.component-card title="Publication">
    <div class="grid gap-6 sm:grid-cols-2">
        @can('resources.publish')
            <x-form.select name="status" label="Status" :options="['draft' => 'Draft', 'published' => 'Published']" :value="$record->status?->value ?? 'draft'" required />
            <x-form.input name="published_at" label="Publication date and time" type="datetime-local" :value="$record->published_at?->format('Y-m-d\TH:i')" help="Leave blank to publish immediately. A future date schedules publication." />
        @else
            <input type="hidden" name="status" value="{{ $record->status?->value ?? 'draft' }}" />
            <p class="text-sm text-gray-500">Publishing permission is required to change published resources.</p>
        @endcan
    </div>
</x-common.component-card>
