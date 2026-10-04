@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Notice">
    <x-slot:actions>
        <x-common.form-actions form-id="notices-form" close-route="admin.notices.index" close-permission="notices.manage" submit-label="Save notice" :disabled="empty($sections)" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="notices-form" method="POST" action="{{ route('admin.notices.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <x-common.form-actions form-id="notices-form" close-route="admin.notices.index" close-permission="notices.manage" submit-label="Save notice" :disabled="empty($sections)" :sticky="true" />

    <x-common.component-card title="Notice details">
        @include('admin.pages.notices._form')
    </x-common.component-card>
</form>
@endsection
