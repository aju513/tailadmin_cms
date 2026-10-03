@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Video">
    <x-slot:actions>
        @can('videos.manage')
            <a href="{{ route('admin.videos.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="videos-form">Save video</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="videos-form" method="POST" action="{{ route('admin.videos.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.pages.videos._form')
</form>
@endsection
