@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Create News">
    <x-slot:actions>
        <x-common.form-actions form-id="news-form" close-route="admin.news.index" close-permission="news.manage" submit-label="Save news" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="news-form" method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
    @csrf
    <x-common.form-actions form-id="news-form" close-route="admin.news.index" close-permission="news.manage" submit-label="Save news" :sticky="true" />
    <x-common.component-card title="New article" desc="Add the story, publishing details, images, and search metadata.">
        @include('admin.pages.news._form')
    </x-common.component-card>
</form>
@endsection
