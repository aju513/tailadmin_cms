@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Resource">
    <x-slot:actions>
        <x-common.form-actions form-id="resources-form" close-route="admin.resources.index" close-permission="resources.manage" submit-label="Save resource" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="resources-form" method="POST" action="{{ route('admin.resources.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <x-common.form-actions form-id="resources-form" close-route="admin.resources.index" close-permission="resources.manage" submit-label="Save resource" :sticky="true" />

    @include('admin.pages.resources._form')
</form>
@endsection
