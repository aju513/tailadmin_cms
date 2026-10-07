@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Popup">
    <x-slot:actions>
        <x-common.form-actions form-id="popups-form" close-route="admin.popups.index" close-permission="popups.manage" submit-label="Save popup" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="popups-form" method="POST" action="{{ route('admin.popups.update', $popup) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <x-common.form-actions form-id="popups-form" close-route="admin.popups.index" close-permission="popups.manage" submit-label="Save popup" :sticky="true" />
    @include('admin.pages.popups._form')
</form>
@endsection
