@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Notice Categories">
    <x-slot:actions>
        @can('notice-categories.create')
            <a href="{{ route('admin.notice-categories.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Add Notice Category</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
@include('pages.admin.categories._manager', ['module' => 'notice-categories'])
@endsection
