<div x-data="{
    activeLanguage: '{{ $errors->has('translations.ne.*') ? 'ne' : 'en' }}',
    activeShared: '{{ $errors->hasAny(['gallery_images', 'gallery_images.*', 'remove_gallery_ids.*']) ? 'gallery' : ($errors->hasAny(['social_media_image', 'social_media_image_alt_text']) ? 'social' : ($errors->hasAny(['thumbnail', 'thumbnail_alt_text']) ? 'thumbnail' : 'banner')) }}',
    stickyActions: false,
    observer: null,
    init() {
        const save = document.querySelector('[data-hall-save]');
        if (!save) return;
        this.observer = new IntersectionObserver(([entry]) => {
            this.stickyActions = !entry.isIntersecting && entry.boundingClientRect.bottom <= 80;
        }, { rootMargin: '-80px 0px 0px 0px' });
        this.observer.observe(save);
    },
    destroy() { this.observer?.disconnect(); }
}" class="space-y-6">
    <div class="sticky top-20 z-30 -mx-4 h-0 sm:-mx-6">
        <div x-show="stickyActions" x-cloak x-transition.opacity.duration.150ms style="left: 0; right: 0; width: 100%;" class="absolute top-0 flex items-center border-b border-gray-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 sm:px-6">
            <div class="ml-auto flex items-center justify-end gap-3">
                @can('halls.manage')<a href="{{ route('admin.halls.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Close</a>@endcan
                <x-ui.button type="submit">{{ $hall->exists ? 'Save changes' : 'Save hall' }}</x-ui.button>
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @if (config('settings.nepali'))
            <x-form.input name="translations[en][title]" label="Hall title (English)" :value="old('translations.en.title', $hall->getTranslation('title', 'en', false))" :error="$errors->first('translations.en.title')" required />
        @else
            <x-form.input name="title" label="Hall title" :value="old('title', $hall->getTranslation('title', 'en', false))" :error="$errors->first('translations.en.title')" required />
        @endif
        <x-form.input name="slug" label="URL slug" :value="old('slug', $hall->slug)" help="Generated from the title if left blank." />
        @if (config('settings.nepali'))
            <x-form.input name="translations[ne][title]" label="Hall title (Nepali)" :value="old('translations.ne.title', $hall->getTranslation('title', 'ne', false))" :error="$errors->first('translations.ne.title')" />
        @endif
        <x-form.input name="building_name" label="Building / Sadan name" :value="old('building_name', $hall->building_name)" />
        <x-form.input name="capacity" type="number" label="Seating capacity" :value="old('capacity', $hall->capacity)" min="1" max="100000" required />
        <x-form.input name="rental_rate" type="number" label="Rental rate (NPR)" :value="old('rental_rate', $hall->rental_rate)" min="0" step="0.01" help="Blank for price on request; zero for free." />
        <x-form.select name="rate_unit" label="Rate unit" :options="config('halls.rate_units')" :value="old('rate_unit', $hall->rate_unit)" help="Required when a rental rate is entered." />
        <x-form.select name="availability_status" label="Availability" :options="config('halls.availability')" :value="old('availability_status', $hall->availability_status)" required />
    </div>
    @can('halls.publish')
        <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $hall->status?->value ?? 'draft') === 'published'" />
    @else
        <input type="hidden" name="status" value="{{ $hall->status?->value ?? 'draft' }}">
    @endcan

    <x-form.multiselect name="amenities[]" label="Amenities" :options="config('halls.amenities')" :value="old('amenities', $hall->amenities ?? [])" />
    @error('amenities.*')<p class="text-sm text-error-600">{{ $message }}</p>@enderror

    <details open data-hall-collapse class="rounded-xl border border-gray-200 p-5 dark:border-gray-800" @toggle="if ($el.open) $nextTick(() => window.dispatchEvent(new Event('page-language-changed')))">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white">Description and booking instructions</summary>
        <div class="mt-5">
    @if (config('settings.nepali'))
        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
            <nav class="flex border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" role="tablist" aria-label="Hall language">
                @foreach (['en' => 'English', 'ne' => 'Nepali'] as $language => $label)
                    <button type="button" id="hall-tab-{{ $language }}" role="tab" aria-controls="hall-panel-{{ $language }}" :aria-selected="activeLanguage === '{{ $language }}'" @click="activeLanguage = '{{ $language }}'; $nextTick(() => window.dispatchEvent(new Event('page-language-changed')))" :class="activeLanguage === '{{ $language }}' ? 'border-brand-500 bg-white text-brand-600 dark:bg-gray-800' : 'border-transparent text-gray-500'" class="inline-flex items-center gap-2 border-b-2 px-5 py-4 text-sm font-medium"><img src="{{ asset('images/flags/'.($language === 'ne' ? 'np' : 'en').'.svg') }}" alt="" class="h-4 w-6 object-contain">{{ $label }}</button>
                @endforeach
            </nav>
            @foreach (['en', 'ne'] as $language)
                <section id="hall-panel-{{ $language }}" role="tabpanel" aria-labelledby="hall-tab-{{ $language }}" x-show="activeLanguage === '{{ $language }}'" @if ($language === 'ne') x-cloak @endif class="space-y-6 p-5 sm:p-6">
                    @include('pages.admin.halls._content', ['language' => $language])
                </section>
            @endforeach
        </div>
    @else
        <div class="space-y-6">@include('pages.admin.halls._content', ['language' => null])</div>
    @endif
        </div>
    </details>

    <details open data-hall-collapse class="rounded-xl border border-gray-200 p-5 dark:border-gray-800" @invalid.capture="$el.open = true">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white">Location, contact and other details</summary>
        <div class="mt-5 grid gap-6 md:grid-cols-2">
            <x-form.input name="location" label="Location" :value="old('location', $hall->location)" />
            <x-form.input name="floor_area" type="number" label="Floor area (m²)" :value="old('floor_area', $hall->floor_area)" min="0.01" step="0.01" />
            <div class="md:col-span-2"><x-form.textarea name="address" label="Full address" :value="old('address', $hall->address)" rows="3" /></div>
            <x-form.input name="map_url" type="url" label="Map / directions URL" :value="old('map_url', $hall->map_url)" />
            <x-form.input name="contact_person" label="Contact person / office" :value="old('contact_person', $hall->contact_person)" />
            <x-form.input name="contact_phone" type="tel" label="Contact phone" :value="old('contact_phone', $hall->contact_phone)" />
            <x-form.input name="contact_email" type="email" label="Contact email" :value="old('contact_email', $hall->contact_email)" />
            <x-form.input name="sort_order" type="number" label="Display order" :value="old('sort_order', $hall->sort_order ?? 0)" min="0" required />
            @can('halls.publish')
                <x-form.date-picker name="published_at" label="Publish date" :value="old('published_at', $hall->published_at?->format('Y-m-d'))" />
            @endcan
        </div>
    </details>

    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
        <nav class="flex flex-wrap border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" role="tablist" aria-label="Hall images">
            @foreach (['thumbnail' => 'Listing Thumbnail', 'banner' => 'Banner Image', 'gallery' => 'Gallery Images', 'social' => 'Social Media Image'] as $tab => $label)
                <button type="button" id="hall-image-tab-{{ $tab }}" role="tab" aria-controls="hall-image-panel-{{ $tab }}" :aria-selected="activeShared === '{{ $tab }}'" @click="activeShared = '{{ $tab }}'" :class="activeShared === '{{ $tab }}' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">{{ $label }}</button>
            @endforeach
        </nav>
        @foreach (['thumbnail' => ['thumbnail', 'thumbnailMedia', 'Listing thumbnail'], 'banner' => ['banner_image', 'bannerMedia', 'Banner image'], 'social' => ['social_media_image', 'socialMedia', 'Social media image']] as $tab => [$input, $relationship, $label])
            <section id="hall-image-panel-{{ $tab }}" role="tabpanel" aria-labelledby="hall-image-tab-{{ $tab }}" x-show="activeShared === '{{ $tab }}'" @if ($tab !== 'banner') x-cloak @endif class="space-y-6 p-5 sm:p-6">
                @if ($hall->{$relationship})
                    <img src="{{ $hall->{$relationship}->url() }}" alt="{{ $hall->{$relationship}->alt_text ?: $hall->title }}" class="h-40 w-full rounded-xl object-cover">
                    <x-form.checkbox :name="'remove_'.$input" :label="'Remove current '.strtolower($label)" :checked="(bool) old('remove_'.$input, false)" />
                @endif
                <x-form.file-upload :name="$input" :label="'Upload '.strtolower($label)" accept="image/jpeg,image/png,image/webp" :max-size="5242880" help="JPG, PNG, or WebP, up to 5 MB." />
                <x-form.input :name="$input.'_alt_text'" :label="$label.' alt text'" :value="old($input.'_alt_text', $hall->{$relationship}?->alt_text)" />
            </section>
        @endforeach
        <section id="hall-image-panel-gallery" role="tabpanel" aria-labelledby="hall-image-tab-gallery" x-show="activeShared === 'gallery'" x-cloak class="space-y-6 p-5 sm:p-6">
            @if ($hall->exists && $hall->galleryImages->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($hall->galleryImages as $image)
                        @if ($image->mediaAsset)
                            <div class="space-y-2"><img src="{{ $image->mediaAsset->url() }}" alt="{{ $image->mediaAsset->alt_text ?: $hall->title }}" class="h-32 w-full rounded-xl object-cover"><x-form.checkbox name="remove_gallery_ids[]" :id="'remove-gallery-'.$image->id" :value="$image->id" label="Remove image" :checked="in_array($image->id, old('remove_gallery_ids', []))" /></div>
                        @endif
                    @endforeach
                </div>
            @endif
            <x-form.file-upload name="gallery_images[]" label="Add gallery images" accept="image/jpeg,image/png,image/webp" :multiple="true" :max-files="config('halls.gallery_limit')" :max-size="5242880" :help="'Up to '.config('halls.gallery_limit').' images per hall, 5 MB each. Images appear in upload order.'" :error="$errors->first('gallery_images') ?: $errors->first('gallery_images.*')" />
            @error('remove_gallery_ids.*')<p class="text-sm text-error-600">{{ $message }}</p>@enderror
        </section>
    </div>

    <details open data-hall-collapse class="rounded-xl border border-gray-200 p-5 dark:border-gray-800">
        <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white">SEO Details</summary>
        <div class="mt-5 space-y-5">
        <x-form.input name="meta_title" label="SEO title" :value="old('meta_title', $hall->meta_title)" />
        <x-form.textarea name="meta_description" label="SEO description" :value="old('meta_description', $hall->meta_description)" />
        </div>
    </details>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-hall-collapse]').forEach(section => {
        const heading = section.querySelector('summary');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let expanded = section.open;
        let animation = null;
        heading.setAttribute('aria-expanded', String(expanded));

        const toggle = next => {
            const start = section.getBoundingClientRect().height;
            if (animation) {
                animation.onfinish = null;
                animation.cancel();
                animation = null;
            }
            expanded = next;
            heading.setAttribute('aria-expanded', String(next));
            section.open = true;

            const styles = getComputedStyle(section);
            const collapsed = heading.getBoundingClientRect().height
                + parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom)
                + parseFloat(styles.borderTopWidth) + parseFloat(styles.borderBottomWidth);
            const end = next ? section.getBoundingClientRect().height : collapsed;

            const finish = () => {
                section.open = expanded;
                section.style.overflow = '';
                animation = null;
                if (expanded) window.dispatchEvent(new Event('page-language-changed'));
            };

            if (reducedMotion.matches || !section.animate) {
                finish();
                return;
            }
            section.style.overflow = 'hidden';
            animation = section.animate([{ height: `${start}px` }, { height: `${end}px` }], {
                duration: 250,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
            });
            animation.onfinish = finish;
        };

        heading.addEventListener('click', event => {
            event.preventDefault();
            toggle(!expanded);
        });
        section.addEventListener('toggle', () => {
            if (!animation) {
                expanded = section.open;
                heading.setAttribute('aria-expanded', String(expanded));
            }
        });
        section.addEventListener('invalid', () => {
            if (!expanded) toggle(true);
        }, true);
    });
});
</script>
@endpush
