@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Video">
    <x-slot:actions>
        <x-common.form-actions form-id="videos-form" close-route="admin.videos.index" close-permission="videos.manage" submit-label="Save video" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="videos-form" method="POST" action="{{ route('admin.videos.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <x-common.form-actions form-id="videos-form" close-route="admin.videos.index" close-permission="videos.manage" submit-label="Save video" :sticky="true" />

    @include('admin.pages.videos._form')
</form>
@endsection
