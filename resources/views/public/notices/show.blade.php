@extends('layouts.public')

@section('content')
<nav class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}">Home</a><span class="mx-2">/</span><a href="{{ route('public.notices.index') }}">Notices</a><span class="mx-2">/</span>{{ $item->title }}</nav>
<article class="rounded-2xl bg-white p-6 shadow-sm sm:p-10">
    <p class="mb-3 text-sm text-gray-500">{{ $item->notice_type->label() }}</p>
    <h1 class="text-3xl font-bold">{{ $item->title }}</h1>
    <time class="mt-3 block text-sm text-gray-500">{{ $item->published_at?->format('d M Y H:i') }}</time>
    @if($item->deadline_at)
        <p class="mt-4 text-sm font-medium text-gray-700">Deadline: {{ $item->deadline_at->format('d M Y H:i') }} @if($item->deadline_at->isPast())<span class="ml-2 text-gray-500">(Deadline passed)</span>@endif</p>
    @endif
    <div class="prose mt-8 max-w-none">{!! $item->description !!}</div>
    @if($item->fileMedia)<a href="{{ $item->fileMedia->url() }}" target="_blank" rel="noopener noreferrer" class="mt-8 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white">View attachment</a>@endif
</article>
@endsection
