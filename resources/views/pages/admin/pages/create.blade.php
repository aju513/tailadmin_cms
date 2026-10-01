@extends('layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Create Page">
    <x-slot:actions><x-ui.button type="submit" form="page-form" data-page-save>Save</x-ui.button></x-slot:actions>
</x-common.page-breadcrumb>
<form id="page-form" method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="p-4 sm:p-6">
        @include('pages.admin.pages._form', ['submitLabel' => 'Save'])
        </div>
    </div>
</form>
@endsection
