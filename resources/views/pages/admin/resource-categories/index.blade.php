@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Resource Categories">
    <x-slot:actions>
        @can('resource-categories.create')
            <a href="{{ route('admin.resource-categories.create') }}" class="inline-flex h-11 items-center rounded-lg bg-brand-500 px-4 text-sm font-medium text-white hover:bg-brand-600">Add Resource Category</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>
@include('pages.admin.categories._manager', ['module' => 'resource-categories'])
@endsection
