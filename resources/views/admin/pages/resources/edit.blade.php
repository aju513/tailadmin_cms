@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Resource">
    <x-slot:actions>
        @can('resources.manage')
            <a href="{{ route('admin.resources.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="resources-form">Save resource</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="resources-form" method="POST" action="{{ route('admin.resources.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    @include('admin.pages.resources._form')
</form>
@endsection
