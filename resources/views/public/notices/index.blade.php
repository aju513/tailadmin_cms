@extends('layouts.public')

@section('content')
<nav class="mb-6 text-sm text-gray-500"><a href="{{ route('public.home') }}">Home</a><span class="mx-2">/</span>Notices</nav>
<h1 class="mb-6 text-3xl font-bold">Notices</h1>
<form method="GET" action="{{ route('public.notices.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
    <div class="w-64"><x-form.select name="notice_category_id" label="Notice category" :options="$categories" :value="request('notice_category_id')" placeholder="All categories" /></div>
    <x-ui.button type="submit" variant="outline">Show notices</x-ui.button>
</form>
@include('public.notices._listing')
@endsection
