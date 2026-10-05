<x-common.component-card title="Slide details">
    <div class="grid gap-6 sm:grid-cols-2">
        <x-form.input name="title" label="Title" :value="$slide->title" maxlength="255" required />
        <x-form.input name="subtitle" label="Subtitle" :value="$slide->subtitle" maxlength="500" />
        <div class="sm:col-span-2"><x-form.input name="link_url" label="Link URL" type="url" :value="$slide->link_url" maxlength="1000" /></div>
    </div>
    <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $slide->status?->value ?? 'draft') === 'published'" />
</x-common.component-card>
<x-common.component-card title="Slide image">
    @if($slide->media)
        <img src="{{ $slide->media->url() }}" alt="{{ $slide->title }}" class="max-h-64 w-full rounded-xl object-cover" />
    @endif
    <x-form.file-upload name="image" label="Image" upload-profile="images.homepage_slide" :required="! $slide->exists" help="Uploading a new image replaces the current slide image." />
</x-common.component-card>
