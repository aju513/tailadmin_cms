<div x-data="{ activeImage: '{{ $errors->hasAny(['thumbnail', 'thumbnail_alt_text']) ? 'thumbnail' : ($errors->hasAny(['social_media_image', 'social_media_alt_text']) ? 'social' : 'banner') }}' }" class="space-y-8">
    <div class="grid gap-6 md:grid-cols-2">
        <x-form.input name="title" label="News title" :value="old('title', $item->title)" required />
        <x-form.input name="slug" label="URL slug" :value="old('slug', $item->slug)" help="Leave blank to generate from the title." />
        <x-form.input name="subtitle" label="Subtitle" :value="old('subtitle', $item->subtitle)" />
        <x-form.date-picker name="published_at" label="Publish date" :value="old('published_at', $item->published_at?->format('Y-m-d'))" help="A future date schedules visibility after publication." />
        <div class="md:col-span-2 flex flex-wrap items-center gap-x-8 gap-y-4">
            @can('news.publish')
                <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $item->status?->value ?? 'draft') === 'published'" />
            @else
                <input type="hidden" name="status" value="{{ $item->status?->value ?? 'draft' }}">
                <p class="text-sm text-gray-500">Publishing requires the news publishing permission.</p>
            @endcan
            <x-form.toggle name="featured" label="Featured" :checked="old('featured', $item->featured)" />
        </div>
    </div>

    <div class="space-y-6 border-t border-gray-200 pt-6 dark:border-gray-800">
        <x-form.editor name="excerpt" label="Excerpt" :value="old('excerpt', $item->excerpt)" placeholder="A short introduction for the news listing..." />
        <x-form.editor name="body" label="Article content" :value="old('body', $item->body)" placeholder="Write the full article..." />
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
        <nav class="flex flex-wrap border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" role="tablist" aria-label="News images">
            <button type="button" id="news-image-tab-thumbnail" role="tab" aria-controls="news-image-panel-thumbnail" :aria-selected="activeImage === 'thumbnail'" @click="activeImage = 'thumbnail'" :class="activeImage === 'thumbnail' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Listing Thumbnail</button>
            <button type="button" id="news-image-tab-banner" role="tab" aria-controls="news-image-panel-banner" :aria-selected="activeImage === 'banner'" @click="activeImage = 'banner'" :class="activeImage === 'banner' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Banner Image</button>
            <button type="button" id="news-image-tab-social" role="tab" aria-controls="news-image-panel-social" :aria-selected="activeImage === 'social'" @click="activeImage = 'social'" :class="activeImage === 'social' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">Social Media Image</button>
        </nav>

        <section id="news-image-panel-thumbnail" role="tabpanel" aria-labelledby="news-image-tab-thumbnail" x-show="activeImage === 'thumbnail'" class="space-y-6 p-5 sm:p-6">
            @if($item->thumbnailMedia)<img src="{{ $item->thumbnailMedia->url() }}" alt="{{ $item->thumbnailMedia->alt_text ?: $item->title }}" class="h-40 w-full rounded-xl object-cover">@endif
            <x-form.file-upload name="thumbnail" label="Upload listing thumbnail" accept="image/*" :max-size="5242880" />
            <x-form.input name="thumbnail_alt_text" label="Thumbnail alt text" :value="old('thumbnail_alt_text', $item->thumbnailMedia?->alt_text)" />
        </section>
        <section id="news-image-panel-banner" role="tabpanel" aria-labelledby="news-image-tab-banner" x-show="activeImage === 'banner'" x-cloak class="space-y-6 p-5 sm:p-6">
            @if($item->bannerMedia)<img src="{{ $item->bannerMedia->url() }}" alt="{{ $item->bannerMedia->alt_text ?: $item->title }}" class="h-40 w-full rounded-xl object-cover">@endif
            <x-form.file-upload name="banner_image" label="Upload banner image" accept="image/*" :max-size="5242880" />
            <x-form.input name="banner_alt_text" label="Banner alt text" :value="old('banner_alt_text', $item->bannerMedia?->alt_text)" />
        </section>
        <section id="news-image-panel-social" role="tabpanel" aria-labelledby="news-image-tab-social" x-show="activeImage === 'social'" x-cloak class="space-y-6 p-5 sm:p-6">
            @if($item->socialMedia)<img src="{{ $item->socialMedia->url() }}" alt="{{ $item->socialMedia->alt_text ?: $item->title }}" class="h-40 w-full rounded-xl object-cover">@endif
            <x-form.file-upload name="social_media_image" label="Upload social media image" accept="image/*" :max-size="5242880" />
            <x-form.input name="social_media_alt_text" label="Social media alt text" :value="old('social_media_alt_text', $item->socialMedia?->alt_text)" />
        </section>
    </div>

    <div class="space-y-6 border-t border-gray-200 pt-6 dark:border-gray-800">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white">SEO details</h2>
        <x-form.input name="meta_title" label="SEO title" :value="old('meta_title', $item->meta_title)" />
        <x-form.textarea name="meta_description" label="SEO description" :value="old('meta_description', $item->meta_description)" />
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.news.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</a>
        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>
</div>
