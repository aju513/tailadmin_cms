@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit News" />
<form method="POST" action="{{ route('admin.news.update', $item) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-common.component-card title="Edit article" desc="Update the story and its public presentation.">
        @include('admin.pages.news._form', ['submitLabel' => 'Save changes'])
    </x-common.component-card>
</form>
@endsection
