@extends('layouts.app')
@section('content')<x-common.page-breadcrumb pageTitle="Create Page" /><form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">@csrf<x-common.component-card title="Create page" desc="Create a nested public page.">@include('pages.admin.pages._form', ['submitLabel' => 'Create page'])</x-common.component-card></form>@endsection
