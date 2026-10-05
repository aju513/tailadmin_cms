@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Homepage Slide">
    <x-slot:actions>
        <x-common.form-actions form-id="homepage-slides-form" close-route="admin.homepage-slides.index" close-permission="homepage-slides.manage" submit-label="Save slide" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="homepage-slides-form" method="POST" action="{{ route('admin.homepage-slides.update', $slide) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <x-common.form-actions form-id="homepage-slides-form" close-route="admin.homepage-slides.index" close-permission="homepage-slides.manage" submit-label="Save slide" :sticky="true" />
    @include('admin.pages.homepage-slides._form')
</form>
@endsection
