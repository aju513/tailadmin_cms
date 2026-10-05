@php($slugState = ['title' => old('title', $record->title), 'slug' => old('slug', $record->slug), 'existing' => $record->exists])
<x-common.component-card title="Gallery details" :x-data="'slugEditor('.\Illuminate\Support\Js::from($slugState).')'">
    <div class="grid gap-6 sm:grid-cols-2">
        <x-form.input name="title" label="Title" :value="$record->title" x-bind:value="title" @input="updateTitle($event.target.value)" required maxlength="255" />
        <x-form.input name="slug" label="URL slug" :value="$record->slug" x-bind:value="slug" @input="updateSlug($event.target.value)" maxlength="255" />
    </div>
    @can('gallery.publish')
        <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $record->status?->value ?? 'draft') === 'published'" help="Publication date and time are set automatically." />
    @else
        <input type="hidden" name="status" value="{{ $record->status?->value ?? 'draft' }}" />
        <p class="text-sm text-gray-500 dark:text-gray-400">Status: {{ ucfirst($record->status?->value ?? 'draft') }}.</p>
    @endcan
</x-common.component-card>
<x-common.component-card title="Gallery images">
    @include('admin.pages.gallery._photos')
</x-common.component-card>
