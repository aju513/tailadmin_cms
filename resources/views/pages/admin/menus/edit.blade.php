@extends('layouts.app')
@section('content')<x-common.page-breadcrumb pageTitle="Edit Menu Item" /><form method="POST" action="{{ route('admin.menus.update', $item) }}">@csrf @method('PUT')<x-common.component-card title="Edit menu item">@include('pages.admin.menus._form', ['submitLabel' => 'Save changes'])</x-common.component-card></form>@endsection
