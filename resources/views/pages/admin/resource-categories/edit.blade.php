@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Resource Category">
    <x-slot:actions>
        @can('resource-categories.manage')
            <a href="{{ route('admin.resource-categories.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="resource-categories-form">Save</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="resource-categories-form" method="POST" action="{{ route('admin.resource-categories.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    @include('pages.admin.resource-categories._form')
</form>
@endsection
