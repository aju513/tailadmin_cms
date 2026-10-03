<x-common.component-card title="Gallery details">
    <div class="space-y-6">
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="sm:col-span-2"><x-form.input name="title" label="Title" :value="$record->title" required maxlength="255" /></div>
            <x-form.input name="slug" label="URL slug" :value="$record->slug" help="Leave blank to generate from the title. Existing slugs stay unchanged." />
            <div class="flex items-center sm:pt-6">
                @can('gallery.publish')
                    <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $record->status?->value ?? 'draft') === 'published'" help="Publication date and time are set automatically." />
                @else
                    <input type="hidden" name="status" value="{{ $record->status?->value ?? 'draft' }}" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status: {{ ucfirst($record->status?->value ?? 'draft') }}.</p>
                @endcan
            </div>
        </div>
        @include('admin.pages.gallery._photos')
    </div>
</x-common.component-card>
