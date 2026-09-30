@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Gallery">
    <x-slot:actions>
        @can('gallery.manage')
            <a href="{{ route('admin.gallery.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="gallery-form">Save gallery</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="gallery-form" method="POST" action="{{ route('admin.gallery.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    @include('pages.admin.gallery._form')
</form>
@endsection
