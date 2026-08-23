@extends('layouts.app')
@section('content')<x-common.page-breadcrumb pageTitle="Create Menu Item" /><form method="POST" action="{{ route('admin.menus.store') }}">@csrf<x-common.component-card title="Create menu item">@include('pages.admin.menus._form', ['item' => null, 'submitLabel' => 'Create item'])</x-common.component-card></form>@endsection
