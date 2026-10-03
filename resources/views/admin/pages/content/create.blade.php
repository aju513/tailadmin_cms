@extends('admin.layouts.app')
@section('content')<x-common.page-breadcrumb :pageTitle="$title" /><form method="POST" action="{{ route('admin.'.$resource.'.store') }}">@csrf<x-common.component-card :title="$title">@include('admin.pages.content._form', ['submitLabel' => 'Create '.strtolower($resourceLabel)])</x-common.component-card></form>@endsection
