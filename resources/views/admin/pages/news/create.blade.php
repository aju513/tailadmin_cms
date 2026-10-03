@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Create News" />
<form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
    @csrf
    <x-common.component-card title="New article" desc="Add the story, publishing details, images, and search metadata.">
        @include('admin.pages.news._form', ['submitLabel' => 'Create news'])
    </x-common.component-card>
</form>
@endsection
