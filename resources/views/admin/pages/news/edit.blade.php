@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit News">
    <x-slot:actions>
        @can('news.manage')
            <a href="{{ route('admin.news.index') }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Close</a>
        @endcan
        <x-ui.button type="submit" form="news-form">Save news</x-ui.button>
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="news-form" method="POST" action="{{ route('admin.news.update', $item) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-common.component-card title="Edit article" desc="Update the story and its public presentation.">
        @include('admin.pages.news._form')
    </x-common.component-card>
</form>
@endsection
