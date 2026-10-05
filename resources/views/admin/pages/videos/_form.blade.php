<x-common.component-card title="Video details">
    <div class="grid gap-6 sm:grid-cols-2">
        <x-form.input name="title" label="Title" :value="$record->title" required maxlength="255" />
        <x-form.input name="video_url" label="Video URL" type="url" :value="$record->video_url" required help="Use a YouTube or other HTTPS video link." />
    </div>
    @can('videos.publish')
        <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $record->status?->value ?? 'draft') === 'published'" help="The publication date and time are set automatically." />
    @else
        <input type="hidden" name="status" value="{{ $record->status?->value ?? 'draft' }}" />
        <p class="text-sm text-gray-500 dark:text-gray-400">Publication status: {{ ucfirst($record->status?->value ?? 'draft') }}. Publishing permission is required to change published content.</p>
    @endcan
    <x-form.editor name="description" label="Description" :value="$record->description" placeholder="Write the video description..." />
</x-common.component-card>
<x-common.component-card title="Thumbnail">
    @if($record->coverMedia)
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <img src="{{ $record->coverMedia->url() }}" alt="{{ $record->title }}" class="h-24 w-36 rounded-lg object-cover" />
            <x-form.checkbox name="remove_cover" value="1" label="Remove current image" :checked="(bool) old('remove_cover', false)" />
        </div>
    @endif
    <x-form.file-upload name="cover" label="Thumbnail" upload-profile="images.video_cover" help="Uploading replaces the current image." />
</x-common.component-card>
