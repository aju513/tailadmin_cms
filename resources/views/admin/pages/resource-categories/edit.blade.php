@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Resource Category">
    <x-slot:actions>
        <x-common.form-actions form-id="resource-categories-form" close-route="admin.resource-categories.index" close-permission="resource-categories.manage" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="resource-categories-form" method="POST" action="{{ route('admin.resource-categories.update', $record) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <x-common.form-actions form-id="resource-categories-form" close-route="admin.resource-categories.index" close-permission="resource-categories.manage" :sticky="true" />
    @include('admin.pages.resource-categories._form')
</form>
@endsection
