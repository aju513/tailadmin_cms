<x-common.component-card title="Slide details">
    <x-form.input name="title" label="Title" :value="$slide->title" maxlength="255" required />
    <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $slide->status?->value ?? 'draft') === 'published'" />
</x-common.component-card>
<x-common.component-card title="Slide image">
    @if($slide->media)
        <img src="{{ $slide->media->url() }}" alt="{{ $slide->title }}" class="max-h-64 w-full rounded-xl object-cover" />
    @endif
    <x-form.file-upload name="image" label="Image" upload-profile="images.homepage_slide" :required="! $slide->exists" help="Uploading a new image replaces the current slide image." />
</x-common.component-card>
