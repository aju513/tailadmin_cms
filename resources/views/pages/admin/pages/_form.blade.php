<div x-data="{ activeTab: 'content' }" class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <x-form.input name="title" label="Page title" :value="old('title', $page->title)" required data-page-title />
        <x-form.select name="page_type" label="Page type" required>
            @foreach($pageTypes as $pageType)
                <option value="{{ $pageType->value }}" @selected(old('page_type', $page->page_type?->value ?? 'standard') === $pageType->value)>{{ $pageType->label() }}</option>
            @endforeach
        </x-form.select>
        <div>
            <x-form.input name="slug_preview" label="URL title" :value="old('slug_preview', $page->slug)" readonly data-page-slug />
            <p class="mt-1 text-xs text-gray-500">Generated automatically from the page title.</p>
        </div>
        <x-form.select name="parent_id" label="Parent page">
            <option value="">__SELF</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parent->id)>{{ str_repeat('-- ', substr_count($parent->path, '/')) }}{{ $parent->title }}</option>
            @endforeach
        </x-form.select>
        @can('pages.publish')
            <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $page->status?->value ?? 'draft') === 'published'" />
        @else
            <input type="hidden" name="status" value="draft">
            <p class="self-end text-sm text-gray-500">You can save drafts. Publishing requires publishing permission.</p>
        @endcan
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
        <nav class="flex flex-wrap border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" aria-label="Page content sections">
            <button type="button" @click="activeTab = 'content'" :class="activeTab === 'content' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Description</button>
            <button type="button" @click="activeTab = 'banner'" :class="activeTab === 'banner' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Banner Image</button>
            <button type="button" @click="activeTab = 'social'" :class="activeTab === 'social' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Social Media Image</button>
            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">SEO</button>
        </nav>

        <div x-show="activeTab === 'content'" class="space-y-6 p-5 sm:p-6">
            <x-form.editor name="summary" label="Summary" :value="old('summary', $page->summary)" placeholder="Write a short summary..." />
            <x-form.editor name="body" label="Page content" :value="old('body', $page->body)" placeholder="Write the page content..." />
        </div>

        <div x-show="activeTab === 'banner'" x-cloak class="space-y-6 p-5 sm:p-6">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white">Banner image</h3>
                <p class="mt-1 text-sm text-gray-500">Used for page headers and large social previews.</p>
            </div>
            @if($page->bannerMedia)
                <img src="{{ $page->bannerMedia->url() }}" alt="{{ $page->bannerMedia->alt_text ?: $page->title }}" class="h-40 w-full rounded-xl object-cover">
            @endif
            <x-form.file-upload name="banner_image" label="Upload banner image" accept="image/*" :max-size="5242880" />
            <x-form.input name="banner_alt_text" label="Banner alt text" :value="old('banner_alt_text', $page->bannerMedia?->alt_text)" />
        </div>

        <div x-show="activeTab === 'social'" x-cloak class="space-y-6 p-5 sm:p-6">
            <div>
                <h3 class="text-base font-medium text-gray-800 dark:text-white">Social media image</h3>
                <p class="mt-1 text-sm text-gray-500">Used when this page is shared on social networks.</p>
            </div>
            @if($page->socialMedia)
                <img src="{{ $page->socialMedia->url() }}" alt="{{ $page->socialMedia->alt_text ?: $page->title }}" class="h-40 w-full rounded-xl object-cover">
            @endif
            <x-form.file-upload name="social_media_image" label="Upload social media image" accept="image/*" :max-size="5242880" />
            <x-form.input name="social_media_alt_text" label="Social media alt text" :value="old('social_media_alt_text', $page->socialMedia?->alt_text)" />
        </div>

        <div x-show="activeTab === 'seo'" x-cloak class="grid gap-6 p-5 sm:grid-cols-2 sm:p-6">
            <x-form.input name="meta_title" label="SEO title" :value="old('meta_title', $page->meta_title)" />
            <x-form.textarea name="meta_description" label="SEO description" :value="old('meta_description', $page->meta_description)" />
        </div>
    </div>

    <div class="flex justify-end">
        <button class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white">{{ $submitLabel }}</button>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const title = document.querySelector('[data-page-title]');
    const slug = document.querySelector('[data-page-slug]');

    if (!title || !slug) return;

    const slugify = value => value.toString().toLowerCase().trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const updateSlug = () => { slug.value = slugify(title.value); };
    title.addEventListener('input', updateSlug);
    updateSlug();
});
</script>
@endpush
