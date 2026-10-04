@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit News">
    <x-slot:actions>
        <x-common.form-actions form-id="news-form" close-route="admin.news.index" close-permission="news.manage" submit-label="Save news" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="news-form" method="POST" action="{{ route('admin.news.update', $item) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-common.form-actions form-id="news-form" close-route="admin.news.index" close-permission="news.manage" submit-label="Save news" :sticky="true" />
    <x-common.component-card title="Edit article" desc="Update the story and its public presentation.">
        @include('admin.pages.news._form')
    </x-common.component-card>
</form>
@endsection
