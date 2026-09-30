<div x-data="{ pageType: @js(old('page_type', $page->page_type?->value ?? 'article')), activeLanguage: '{{ $errors->has('translations.ne.*') ? 'ne' : 'en' }}', activeShared: '{{ $errors->hasAny(['social_media_image', 'social_media_alt_text']) ? 'social' : 'banner' }}' }" class="space-y-6">
    <div class="sticky top-20 z-30 -mx-4 flex justify-end border-b border-gray-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 sm:-mx-6 sm:px-6">
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <x-form.select name="page_type" label="Page type" x-model="pageType" required>
            @foreach($pageTypes as $pageType)
                <option value="{{ $pageType->value }}" @selected(old('page_type', $page->page_type?->value ?? 'article') === $pageType->value)>{{ $pageType->label() }}</option>
            @endforeach
        </x-form.select>
        <div x-show="pageType === 'resource'" x-cloak>
            <x-form.select name="resource_category_id" label="Resource category" :options="$resourceCategories" :value="$page->resource_category_id" placeholder="All categories" x-bind:disabled="pageType !== 'resource'" help="Select a category to show its published documents, or All categories to show the whole catalogue." />
        </div>
        <div x-show="pageType === 'notices'" x-cloak>
            <x-form.select name="notice_category_id" label="Notice category" :options="$noticeCategories" :value="$page->notice_category_id" placeholder="All categories" x-bind:disabled="pageType !== 'notices'" help="Show published notices of this category, or All categories to show every active category." />
        </div>
        <x-form.select name="parent_id" label="Parent page">
            <option value="">__SELF</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $page->parent_id) === (string) $parent->id)>{{ str_repeat('-- ', substr_count($parent->path, '/')) }}{{ $parent->title }}</option>
            @endforeach
        </x-form.select>
    </div>

    @if(config('settings.nepali'))
    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
        <nav class="flex border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" role="tablist" aria-label="Page language">
            <button type="button" id="page-tab-en" role="tab" aria-controls="page-panel-en" :aria-selected="activeLanguage === 'en'" @click="activeLanguage = 'en'; $nextTick(() => window.dispatchEvent(new Event('page-language-changed')))" :class="activeLanguage === 'en' ? 'border-brand-500 bg-white text-brand-600 dark:bg-gray-800' : 'border-transparent text-gray-500'" class="inline-flex items-center gap-2 border-b-2 px-5 py-4 text-sm font-medium"><img src="{{ asset('images/flags/en.svg') }}" alt="" class="h-4 w-6 object-contain"> English</button>
            <button type="button" id="page-tab-ne" role="tab" aria-controls="page-panel-ne" :aria-selected="activeLanguage === 'ne'" @click="activeLanguage = 'ne'; $nextTick(() => window.dispatchEvent(new Event('page-language-changed')))" :class="activeLanguage === 'ne' ? 'border-brand-500 bg-white text-brand-600 dark:bg-gray-800' : 'border-transparent text-gray-500'" class="inline-flex items-center gap-2 border-b-2 px-5 py-4 text-sm font-medium"><img src="{{ asset('images/flags/np.svg') }}" alt="" class="h-4 w-5 object-contain"> नेपाली</button>
        </nav>

        <section id="page-panel-en" role="tabpanel" aria-labelledby="page-tab-en" x-show="activeLanguage === 'en'" class="space-y-6 p-5 sm:p-6">
            <div class="grid gap-6 md:grid-cols-2">
                <x-form.input name="translations[en][title]" label="Page title (English)" :value="old('translations.en.title', $page->getTranslation('title', 'en', false))" :error="$errors->first('translations.en.title')" required data-page-title />
                <div>
                    <x-form.input name="slug_preview" label="URL title" :value="$page->slug" readonly data-page-slug />
                    <p class="mt-1 text-xs text-gray-500">Generated from the English page title and shared by both languages.</p>
                </div>
            </div>
            @can('pages.publish')
                <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $page->status?->value ?? 'draft') === 'published'" />
            @else
                <input type="hidden" name="status" value="draft">
                <p class="text-sm text-gray-500">You can save drafts. Publishing requires publishing permission.</p>
            @endcan
            <x-form.editor name="translations[en][summary]" label="Summary (English)" :value="old('translations.en.summary', $page->getTranslation('summary', 'en', false))" :error="$errors->first('translations.en.summary')" placeholder="Write a short summary..." />
            <x-form.editor name="translations[en][body]" label="Page content (English)" :value="old('translations.en.body', $page->getTranslation('body', 'en', false))" :error="$errors->first('translations.en.body')" placeholder="Write the page content..." />
        </section>

        <section id="page-panel-ne" role="tabpanel" aria-labelledby="page-tab-ne" x-show="activeLanguage === 'ne'" x-cloak class="space-y-6 p-5 sm:p-6">
            <x-form.input name="translations[ne][title]" label="Page title (Nepali)" :value="old('translations.ne.title', $page->getTranslation('title', 'ne', false))" :error="$errors->first('translations.ne.title')" placeholder="नेपाली शीर्षक" />
            <x-form.editor name="translations[ne][summary]" label="Summary (Nepali)" :value="old('translations.ne.summary', $page->getTranslation('summary', 'ne', false))" :error="$errors->first('translations.ne.summary')" placeholder="नेपाली सारांश लेख्नुहोस्..." />
            <x-form.editor name="translations[ne][body]" label="Page content (Nepali)" :value="old('translations.ne.body', $page->getTranslation('body', 'ne', false))" :error="$errors->first('translations.ne.body')" placeholder="नेपाली सामग्री लेख्नुहोस्..." />
            <p class="text-xs text-gray-500">Leave Nepali fields blank to show the English content until a translation is ready.</p>
        </section>
    </div>
    @else
        <div class="grid gap-6 md:grid-cols-2">
            <x-form.input name="title" label="Page title" :value="old('title', $page->getTranslation('title', 'en', false))" required data-page-title />
            <div>
                <x-form.input name="slug_preview" label="URL title" :value="$page->slug" readonly data-page-slug />
                <p class="mt-1 text-xs text-gray-500">Generated from the page title.</p>
            </div>
        </div>
        @can('pages.publish')
            <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $page->status?->value ?? 'draft') === 'published'" />
        @else
            <input type="hidden" name="status" value="draft">
            <p class="text-sm text-gray-500">You can save drafts. Publishing requires publishing permission.</p>
        @endcan
        <x-form.editor name="summary" label="Summary" :value="old('summary', $page->getTranslation('summary', 'en', false))" placeholder="Write a short summary..." />
        <x-form.editor name="body" label="Page content" :value="old('body', $page->getTranslation('body', 'en', false))" placeholder="Write the page content..." />
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
        <nav class="flex flex-wrap border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" role="tablist" aria-label="Shared page settings">
            <button type="button" id="page-shared-tab-banner" role="tab" aria-controls="page-shared-panel-banner" :aria-selected="activeShared === 'banner'" @click="activeShared = 'banner'" :class="activeShared === 'banner' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Banner Image</button>
            <button type="button" id="page-shared-tab-social" role="tab" aria-controls="page-shared-panel-social" :aria-selected="activeShared === 'social'" @click="activeShared = 'social'" :class="activeShared === 'social' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Social Media Image</button>
        </nav>

        <section id="page-shared-panel-banner" role="tabpanel" aria-labelledby="page-shared-tab-banner" x-show="activeShared === 'banner'" class="space-y-6 p-5 sm:p-6">
            @if($page->bannerMedia)<img src="{{ $page->bannerMedia->url() }}" alt="{{ $page->bannerMedia->alt_text ?: $page->title }}" class="h-40 w-full rounded-xl object-cover">@endif
            <x-form.file-upload name="banner_image" label="Upload banner image" accept="image/*" :max-size="5242880" />
            <x-form.input name="banner_alt_text" label="Banner alt text" :value="old('banner_alt_text', $page->bannerMedia?->alt_text)" />
        </section>
        <section id="page-shared-panel-social" role="tabpanel" aria-labelledby="page-shared-tab-social" x-show="activeShared === 'social'" x-cloak class="space-y-6 p-5 sm:p-6">
            @if($page->socialMedia)<img src="{{ $page->socialMedia->url() }}" alt="{{ $page->socialMedia->alt_text ?: $page->title }}" class="h-40 w-full rounded-xl object-cover">@endif
            <x-form.file-upload name="social_media_image" label="Upload social media image" accept="image/*" :max-size="5242880" />
            <x-form.input name="social_media_alt_text" label="Social media alt text" :value="old('social_media_alt_text', $page->socialMedia?->alt_text)" />
        </section>
    </div>

    <section class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6" aria-labelledby="page-seo-heading">
        <div>
            <h2 id="page-seo-heading" class="text-base font-semibold text-gray-800 dark:text-white">SEO Details</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search engine title and description for this page.</p>
        </div>
        <x-form.input name="meta_title" label="SEO title" :value="old('meta_title', $page->meta_title)" data-page-seo-title />
        <x-form.textarea name="meta_description" label="SEO description" :value="old('meta_description', $page->meta_description)" />
    </section>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const title = document.querySelector('[data-page-title]');
    const slug = document.querySelector('[data-page-slug]');
    const seoTitle = document.querySelector('[data-page-seo-title]');
    if (!title) return;

    const slugify = value => value.toString().toLowerCase().trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const updateFields = () => {
        if (slug) slug.value = slugify(title.value);
        if (seoTitle) seoTitle.value = title.value;
    };
    title.addEventListener('input', updateFields);
    updateFields();
});
</script>
@endpush
