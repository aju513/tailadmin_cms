@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Hall Details">
    <x-slot:actions><div class="flex gap-3">@can('halls.manage')<a href="{{ route('admin.halls.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:text-gray-300">Back to halls</a>@endcan @can('halls.edit')<a href="{{ route('admin.halls.edit', $hall) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Edit Hall</a>@endcan</div></x-slot:actions>
</x-common.page-breadcrumb>
<div class="space-y-6">
    <x-common.component-card :title="$hall->title">
        @if ($hall->bannerMedia)<img src="{{ $hall->bannerMedia->url() }}" alt="{{ $hall->bannerMedia->alt_text ?: $hall->title }}" class="max-h-80 w-full rounded-xl object-cover">@endif
        <div class="flex flex-wrap gap-2"><x-ui.badge :color="$hall->status->value === 'published' ? 'success' : 'warning'">{{ ucfirst($hall->status->value) }}</x-ui.badge><x-ui.badge :color="$hall->availability_status === 'available' ? 'success' : 'warning'">{{ config('halls.availability.'.$hall->availability_status) }}</x-ui.badge></div>
        <dl class="grid gap-5 text-sm md:grid-cols-3">
            @foreach (['Building' => $hall->building_name, 'Location' => $hall->location, 'Address' => $hall->address, 'Seating capacity' => number_format($hall->capacity).' seats', 'Floor area' => $hall->floor_area ? $hall->floor_area.' m²' : null, 'Rental rate' => $hall->rental_rate !== null ? 'NPR '.number_format((float) $hall->rental_rate, 2).' · '.config('halls.rate_units.'.$hall->rate_unit) : 'Price on request', 'Contact person' => $hall->contact_person, 'Contact phone' => $hall->contact_phone, 'Contact email' => $hall->contact_email, 'URL slug' => $hall->slug, 'Display order' => $hall->sort_order, 'Publish date' => $hall->published_at?->format('M d, Y H:i')] as $label => $value)
                <div><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="mt-1 text-gray-800 dark:text-white">{{ $value ?? '—' }}</dd></div>
            @endforeach
        </dl>
        @if ($hall->map_url)<a href="{{ $hall->map_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-brand-500 hover:underline">View map / directions</a>@endif
        <div><h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white">Amenities</h3><div class="flex flex-wrap gap-2">@forelse ($hall->amenities ?? [] as $amenity)<x-ui.badge color="primary">{{ config('halls.amenities.'.$amenity, $amenity) }}</x-ui.badge>@empty<p class="text-sm text-gray-500">No amenities selected.</p>@endforelse</div></div>
    </x-common.component-card>
    @foreach (['summary' => 'Summary', 'body' => 'Hall description', 'booking_instructions' => 'Booking instructions'] as $field => $label)
        <x-common.component-card :title="$label"><p class="whitespace-pre-line text-sm leading-7 text-gray-600 dark:text-gray-400">{{ trim(html_entity_decode(strip_tags($hall->{$field} ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: 'Not provided.' }}</p></x-common.component-card>
    @endforeach
    <x-common.component-card title="Hall images">
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach (['thumbnailMedia' => 'Listing thumbnail', 'socialMedia' => 'Social media image'] as $relationship => $label)
                @if ($hall->{$relationship})<figure><img src="{{ $hall->{$relationship}->url() }}" alt="{{ $hall->{$relationship}->alt_text ?: $hall->title }}" class="h-40 w-full rounded-xl object-cover"><figcaption class="mt-2 text-xs text-gray-500">{{ $label }}</figcaption></figure>@endif
            @endforeach
            @foreach ($hall->galleryImages as $image)
                @if ($image->mediaAsset)<img src="{{ $image->mediaAsset->url() }}" alt="{{ $image->mediaAsset->alt_text ?: $hall->title }}" class="h-40 w-full rounded-xl object-cover">@endif
            @endforeach
        </div>
        @if (! $hall->thumbnailMedia && ! $hall->socialMedia && $hall->galleryImages->isEmpty())<p class="text-sm text-gray-500">No listing, social, or gallery images uploaded.</p>@endif
    </x-common.component-card>
    <x-common.component-card title="SEO Details"><dl class="space-y-4 text-sm"><div><dt class="text-gray-500">SEO title</dt><dd class="mt-1 text-gray-800 dark:text-white">{{ $hall->meta_title ?? '—' }}</dd></div><div><dt class="text-gray-500">SEO description</dt><dd class="mt-1 text-gray-800 dark:text-white">{{ $hall->meta_description ?? '—' }}</dd></div></dl></x-common.component-card>
</div>
@endsection
