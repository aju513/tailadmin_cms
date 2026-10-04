@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Gallery">
    <x-slot:actions>
        <x-common.form-actions form-id="gallery-form" close-route="admin.gallery.index" close-permission="gallery.manage" submit-label="Save gallery" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="gallery-form" method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <x-common.form-actions form-id="gallery-form" close-route="admin.gallery.index" close-permission="gallery.manage" submit-label="Save gallery" :sticky="true" />

    @include('admin.pages.gallery._form')
</form>
@endsection
