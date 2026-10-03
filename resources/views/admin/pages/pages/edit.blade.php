@extends('admin.layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Edit Page">
    <x-slot:actions><x-ui.button type="submit" form="page-form" data-page-save>Save</x-ui.button></x-slot:actions>
</x-common.page-breadcrumb>
<form id="page-form" method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="p-4 sm:p-6">
        @include('admin.pages.pages._form', ['submitLabel' => 'Save'])
        </div>
    </div>
</form>
@endsection
