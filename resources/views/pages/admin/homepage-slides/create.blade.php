@extends('layouts.app')
@section('content')<x-common.page-breadcrumb pageTitle="Create Homepage Slide" /><form method="POST" action="{{ route('admin.homepage-slides.store') }}" enctype="multipart/form-data">@csrf<x-common.component-card title="Create homepage slide">@include('pages.admin.homepage-slides._form', ['submitLabel' => 'Create slide'])</x-common.component-card></form>@endsection
