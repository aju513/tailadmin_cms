@php($slugState = ['title' => old('title', $record->title), 'slug' => old('slug', $record->slug), 'existing' => $record->exists])
<x-common.component-card title="Resource details" :x-data="'slugEditor('.\Illuminate\Support\Js::from($slugState).')'">
    <div class="grid gap-6 sm:grid-cols-2">
        <x-form.input name="title" label="Title" :value="$record->title" x-bind:value="title" @input="updateTitle($event.target.value)" required maxlength="255" />
        <x-form.input name="slug" label="URL slug" :value="$record->slug" x-bind:value="slug" @input="updateSlug($event.target.value)" maxlength="255" />
        <x-form.select name="resource_category_id" label="Resource category" :options="$categories" :value="$record->resource_category_id" required />
        @can('resources.publish')
            <x-form.date-picker name="published_at" label="Publication date" :value="old('published_at', $record->published_at?->format('Y-m-d'))" help="Leave blank to publish immediately. A future date schedules publication." />
            <div class="flex items-center sm:col-start-1 sm:pt-6">
                <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $record->status?->value ?? 'draft') === 'published'" />
            </div>
        @else
            <input type="hidden" name="status" value="{{ $record->status?->value ?? 'draft' }}" />
            <p class="text-sm text-gray-500">Publishing permission is required to change published resources.</p>
        @endcan
    </div>
    <x-form.editor name="description" label="Description" :value="$record->description" placeholder="Write the resource description..." />
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
