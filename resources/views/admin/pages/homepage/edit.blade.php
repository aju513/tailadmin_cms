@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Homepage">
    <x-slot:actions>
        @can('pages.manage')
            <a href="{{ route('admin.pages.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @elsecan('dashboard.view')
            <a href="{{ route('admin.dashboard') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        @can('homepage.edit')
            <x-ui.button type="submit" form="homepage-form" data-homepage-save>Save homepage</x-ui.button>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>

@php
    $editorState = [
        'title' => old(config('settings.nepali') ? 'translations.en.title' : 'title', $content->getTranslation('title', 'en', false)),
        'originalTitle' => $content->getTranslation('title', 'en', false),
        'seoTitle' => old('meta_title', $content->meta_title),
        'activePanel' => $errors->hasAny(['gallery_images', 'gallery_images.*', 'remove_gallery_ids', 'remove_gallery_ids.*']) ? 'gallery' : ($errors->hasAny(['social_media_image', 'social_media_alt_text', 'remove_social_media_image']) ? 'social' : ($errors->hasAny(['meta_title', 'meta_keywords', 'meta_description']) ? 'seo' : 'content')),
        'activeLanguage' => $errors->has('translations.ne.*') ? 'ne' : 'en',
    ];
@endphp
<form id="homepage-form" method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" x-data="homepageEditor(@js($editorState))" @invalid.capture="revealInvalidField($event)" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="sticky top-20 z-30 -mx-4 h-0 sm:-mx-6">
        <div data-homepage-sticky-actions x-show="stickyActions" x-cloak x-transition.opacity.duration.150ms style="left: 0; right: 0; width: 100%;" class="absolute top-0 flex items-center border-b border-gray-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 sm:px-6">
            <div class="ml-auto flex items-center justify-end gap-3">
                @can('pages.manage')
                    <a href="{{ route('admin.pages.index') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Close</a>
                @elsecan('dashboard.view')
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Close</a>
                @endcan
                @can('homepage.edit')
                    <x-ui.button type="submit" form="homepage-form">Save homepage</x-ui.button>
                @endcan
            </div>
        </div>
    </div>
    <x-common.component-card title="Homepage content" desc="Manage the welcome text, gallery, and search metadata shown on the homepage.">
        <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">Homepage URL: <a href="{{ route('public.home') }}" target="_blank" rel="noopener" class="text-brand-600 underline">/</a></p>
        <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
            <nav class="flex flex-wrap border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900" role="tablist" aria-label="Homepage content sections">
                @foreach(['content' => 'Description', 'gallery' => 'Gallery Images', 'social' => 'Social Media Image', 'seo' => 'SEO Details'] as $panel => $label)
                    <button type="button" id="homepage-tab-{{ $panel }}" role="tab" aria-controls="homepage-panel-{{ $panel }}" :aria-selected="activePanel === '{{ $panel }}'" @click="activePanel = '{{ $panel }}'; $nextTick(() => window.dispatchEvent(new Event('page-language-changed')))" :class="activePanel === '{{ $panel }}' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-5 py-4 text-sm font-medium">{{ $label }}</button>
                @endforeach
            </nav>

            <section id="homepage-panel-content" data-homepage-panel="content" role="tabpanel" aria-labelledby="homepage-tab-content" x-show="activePanel === 'content'" class="space-y-6 p-5 sm:p-6">
                @if(config('settings.nepali'))
                    <nav class="flex gap-2 border-b border-gray-200 dark:border-gray-800" role="tablist" aria-label="Homepage language">
                        @foreach(['en' => 'English', 'ne' => 'Nepali'] as $language => $label)
                            <button type="button" id="homepage-language-tab-{{ $language }}" role="tab" aria-controls="homepage-language-panel-{{ $language }}" :aria-selected="activeLanguage === '{{ $language }}'" @click="activeLanguage = '{{ $language }}'; $nextTick(() => window.dispatchEvent(new Event('page-language-changed')))" :class="activeLanguage === '{{ $language }}' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500'" class="border-b-2 px-4 py-3 text-sm font-medium">{{ $label }}</button>
                        @endforeach
                    </nav>
                    @foreach(['en', 'ne'] as $language)
                        <div id="homepage-language-panel-{{ $language }}" data-language="{{ $language }}" role="tabpanel" aria-labelledby="homepage-language-tab-{{ $language }}" x-show="activeLanguage === '{{ $language }}'" @if($language === 'ne') x-cloak @endif class="space-y-6">
                            @include('admin.pages.homepage._content', ['language' => $language])
                        </div>
                    @endforeach
                @else
                    @include('admin.pages.homepage._content', ['language' => null])
                @endif
            </section>

            <section id="homepage-panel-gallery" data-homepage-panel="gallery" role="tabpanel" aria-labelledby="homepage-tab-gallery" x-show="activePanel === 'gallery'" x-cloak class="space-y-6 p-5 sm:p-6">
                @include('admin.pages.homepage._gallery')
            </section>

            <section id="homepage-panel-social" data-homepage-panel="social" role="tabpanel" aria-labelledby="homepage-tab-social" x-show="activePanel === 'social'" x-cloak class="space-y-6 p-5 sm:p-6">
                @if($content->socialMedia)
                    <img src="{{ $content->socialMedia->url() }}" alt="{{ $content->socialMedia->alt_text ?: $content->title }}" class="h-40 w-full rounded-xl object-cover">
                    <x-form.checkbox name="remove_social_media_image" label="Remove current social media image" :checked="(bool) old('remove_social_media_image', false)" />
                @endif
                <x-form.file-upload name="social_media_image" label="Upload social media image" upload-profile="images.homepage.social" />
                <x-form.input name="social_media_alt_text" label="Social media image alt text" :value="old('social_media_alt_text', $content->socialMedia?->alt_text)" maxlength="255" />
            </section>

            <section id="homepage-panel-seo" data-homepage-panel="seo" role="tabpanel" aria-labelledby="homepage-tab-seo" x-show="activePanel === 'seo'" x-cloak class="space-y-6 p-5 sm:p-6">
                <x-form.input name="meta_title" label="SEO title" :value="old('meta_title', $content->meta_title)" x-bind:value="seoTitle" @input="updateSeoTitle($event.target.value)" maxlength="255" />
                <x-form.input name="meta_keywords" label="SEO keywords" :value="old('meta_keywords', $content->meta_keywords)" maxlength="500" />
                <x-form.textarea name="meta_description" label="SEO description" :value="old('meta_description', $content->meta_description)" maxlength="1000" />
            </section>
        </div>
    </x-common.component-card>
</form>
@endsection
