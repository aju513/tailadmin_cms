@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Notice">
    <x-slot:actions>
        @can('notices.manage')
            <a href="{{ route('admin.notices.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="notices-form" :disabled="empty($sections)">Save notice</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="notices-form" method="POST" action="{{ route('admin.notices.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <x-common.component-card title="Notice details">
        @include('admin.pages.notices._form')
    </x-common.component-card>
</form>
@endsection
