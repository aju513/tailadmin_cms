@extends('layouts.public')
@section('content')
    @if($slides->isNotEmpty())
        <section class="mb-10 overflow-hidden rounded-2xl bg-brand-600 text-white">
            @foreach($slides as $slide)
                <div class="p-10">
                    <h1 class="text-3xl font-bold">{{ $slide->title }}</h1>

                    @if($slide->subtitle)
                        <p class="mt-3 max-w-2xl text-brand-50">{{ $slide->subtitle }}</p>
                    @endif

                    @if($slide->link_url)
                        <a class="mt-6 inline-block rounded-lg bg-white px-4 py-2 font-medium text-brand-700" href="{{ $slide->link_url }}">Learn more</a>
                    @endif
                </div>
            @endforeach
        </section>
    @else
        <section class="mb-10 rounded-2xl bg-brand-600 p-10 text-white"><h1 class="text-3xl font-bold">{{ $settings['office_name'] ?? $settings['site_name'] ?? config('app.name') }}</h1><p class="mt-3">Official information and public services.</p></section>
    @endif
    <section><h2 class="text-2xl font-semibold">Information</h2><div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($featuredPages as $page)<a href="{{ route('public.page', ['path' => $page->path]) }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-400"><h3 class="font-semibold">{{ $page->title }}</h3>@if($page->summary)<p class="mt-2 text-sm text-gray-600">{{ strip_tags($page->summary) }}</p>@endif</a>@empty<p class="text-gray-600">Content is being prepared.</p>@endforelse
    </div></section>
@endsection
