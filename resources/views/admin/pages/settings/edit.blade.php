@extends('admin.layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Site Settings"><x-slot:actions><x-ui.button type="submit" form="site-settings-form">Save settings</x-ui.button></x-slot:actions></x-common.page-breadcrumb>
<form id="site-settings-form" method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">@csrf @method('PUT')
    @if($errors->any())<x-common.component-card title="Please check these fields"><ul class="space-y-1 text-sm text-error-500" role="alert">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-common.component-card>@endif
    <x-common.component-card title="Site identity" desc="The public header, footer, and organization metadata use these details."><div class="grid gap-6 md:grid-cols-2">
        <x-form.input name="site_name" label="Site name" :value="$settings['site_name']" required /><x-form.input name="office_name" label="Office name" :value="$settings['office_name']" />
        <x-form.input name="logo_url" label="Logo URL" :value="$settings['logo_url']" :help="\App\Support\UploadProfile::dimensionHint('images.site_logo').' Leave empty to use the configured emblem.'" /><x-form.input name="phone" label="Phone" :value="$settings['phone']" />
        <x-form.input name="email" label="Email" type="email" :value="$settings['email']" /><x-form.input name="address" label="Address" :value="$settings['address']" />
        <x-form.input name="province_name" label="Province name" :value="$settings['province_name']" />
        <x-form.input name="contact_officer_phone" label="Contact officer phone" :value="$settings['contact_officer_phone']" />
        <x-form.input name="contact_officer_photo_url" label="Contact officer photo URL" type="url" :value="$settings['contact_officer_photo_url']" :help="\App\Support\UploadProfile::dimensionHint('images.contact_officer')" />
        <div class="md:col-span-2"><x-form.textarea name="office_hours" label="Office hours" :value="$settings['office_hours']" /></div>
        <div class="md:col-span-2"><x-form.textarea name="footer_text" label="Footer text" :value="$settings['footer_text']" /></div>
    </div></x-common.component-card>
    <x-common.component-card title="Homepage" desc="Banner photos use the existing Home Slides module. Published catalogues fill the homepage sections automatically."><div class="grid gap-6 md:grid-cols-2">
        <div class="md:col-span-2"><x-form.input name="hero_title" label="Banner heading" :value="$settings['hero_title']" /></div>
        <div class="md:col-span-2"><x-form.textarea name="hero_description" label="Banner description" :value="$settings['hero_description']" /></div>
        @can('homepage.manage')<p class="md:col-span-2 text-sm text-gray-500 dark:text-gray-400">Manage welcome text and gallery images in <a href="{{ route('admin.homepage.edit') }}" class="text-brand-600 underline">Homepage content</a>.</p>@endcan
        <x-form.input name="about_url" label="About page URL" type="url" :value="$settings['about_url']" />
    </div></x-common.component-card>
    @include('admin.pages.settings.design-fields')
    <x-common.component-card title="reCAPTCHA v3" desc="Protect public grievance submissions with Google reCAPTCHA v3.">
        <div class="grid gap-6 md:grid-cols-2">
            <x-form.input name="recaptcha_site_key" label="Site key" :value="$settings['recaptcha_site_key']" maxlength="255" help="Register the public website domain in the Google reCAPTCHA console." />
            <x-form.input name="recaptcha_secret_key" label="Secret key" type="password" :value="''" maxlength="255" autocomplete="new-password" :help="$recaptchaConfigured ? 'A secret key is saved. Leave blank to keep it, or enter a replacement.' : 'Enter the matching secret key. It is encrypted and never displayed.'" />
        </div>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Grievance submissions become available when both keys are configured. Use score-based reCAPTCHA v3 keys.</p>
    </x-common.component-card>
    <x-common.component-card title="Links and SEO" desc="Set real links for training, office directions, and social profiles."><div class="grid gap-6 md:grid-cols-2">
        @foreach(['training_url'=>'Training listing URL','tmis_url'=>'TMIS login URL','map_url'=>'Office map URL','facebook_url'=>'Facebook URL','youtube_url'=>'YouTube URL','linkedin_url'=>'LinkedIn URL','instagram_url'=>'Instagram URL','x_url'=>'X URL'] as $key=>$label)<x-form.input :name="$key" :label="$label" :value="$settings[$key]" type="url" />@endforeach
        <div class="md:col-span-2"><x-form.textarea name="meta_description" label="Default meta description" :value="$settings['meta_description']" maxlength="320" /></div>
    </div><div class="mt-6 text-sm text-gray-500 dark:text-gray-400">The public XML sitemap updates automatically at <a class="text-brand-500" href="{{ route('public.sitemap.index') }}" target="_blank" rel="noopener noreferrer">/sitemap.xml</a>. Canonical URLs use APP_URL.</div></x-common.component-card>
</form>
@endsection
