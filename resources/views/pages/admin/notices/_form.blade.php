<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <x-form.input name="title" label="Notice title" :value="old('title', $item->title)" required />
        <x-form.input name="slug" label="URL slug" :value="old('slug', $item->slug)" help="Leave blank to generate from the title." />
        <x-form.select name="notice_category_id" label="Notice category" :options="$categories" :value="$item->notice_category_id" required />
        <x-form.input name="deadline_at" label="Deadline (optional)" type="datetime-local" :value="$item->deadline_at?->format('Y-m-d\TH:i')" help="For tender submissions or applications. Leave blank when there is no deadline." />
        <div class="flex items-center sm:pt-6">
    @can('notices.publish')
        <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $item->status?->value ?? 'draft') === 'published'" />
    @else
        <input type="hidden" name="status" value="{{ $item->status?->value ?? 'draft' }}">
    @endcan
        </div>
        <x-form.date-picker name="published_at" label="Publish date" :value="old('published_at', $item->published_at?->format('Y-m-d'))" />
    </div>
    <x-form.editor name="description" label="Description" :value="old('description', $item->description)" placeholder="Write the notice description..." />

    <div class="space-y-3 border-t border-gray-200 pt-6 dark:border-gray-800">
        @if($item->fileMedia)<a href="{{ $item->fileMedia->url() }}" target="_blank" rel="noopener" class="text-sm text-brand-600 underline">Current attachment: {{ $item->fileMedia->original_name }}</a>@endif
        <x-form.file-upload name="file" label="Attachment" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx" :max-size="10485760" help="Optional image or office document, up to 10 MB." />
    </div>
    <div class="space-y-5 border-t border-gray-200 pt-6 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white">SEO Details</h2>
        <x-form.input name="meta_title" label="SEO title" :value="old('meta_title', $item->meta_title)" />
        <x-form.textarea name="meta_description" label="SEO description" :value="old('meta_description', $item->meta_description)" />
    </div>
</div>
