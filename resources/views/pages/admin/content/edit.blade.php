@extends('layouts.app')
@section('content')<x-common.page-breadcrumb :pageTitle="$title" /><form method="POST" action="{{ route('admin.'.$resource.'.update', $item) }}">@csrf @method('PUT')<x-common.component-card :title="$title">@include('pages.admin.content._form', ['submitLabel' => 'Save changes'])</x-common.component-card></form>@endsection
