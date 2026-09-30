@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Notice Category">
    <x-slot:actions>
        @can('notice-categories.manage')
            <a href="{{ route('admin.notice-categories.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="notice-categories-form">Save</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="notice-categories-form" method="POST" action="{{ route('admin.notice-categories.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    @include('pages.admin.notice-categories._form')
</form>
@endsection
