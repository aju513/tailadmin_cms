@extends('layouts.public')

@section('content')
<nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}" class="hover:text-brand-600">Home</a><span class="mx-2">/</span>{{ $resource->title }}</nav>
<article class="rounded-2xl bg-white p-6 shadow-sm sm:p-10">
    <p class="mb-3 text-sm text-gray-500">{{ $resource->category->name }} @if($resource->published_at) ? {{ $resource->published_at->format('d M Y') }} @endif</p>
    <h1 class="text-3xl font-bold">{{ $resource->title }}</h1>
    @if($resource->description)<p class="mt-6 whitespace-pre-line text-gray-700">{{ $resource->description }}</p>@endif
    <div class="mt-8">
        <a href="{{ route('public.resources.download', $resource->slug) }}" class="inline-flex rounded-lg bg-brand-500 px-5 py-3 font-medium text-white hover:bg-brand-600">Download document</a>
        @if($resource->fileMedia)<p class="mt-3 text-sm text-gray-500">{{ $resource->fileMedia->original_name }} ? {{ number_format($resource->fileMedia->size / 1024 / 1024, 2) }} MB</p>@endif
    </div>
</article>
@endsection
