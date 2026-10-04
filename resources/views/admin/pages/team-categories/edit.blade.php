@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Team Category">
    <x-slot:actions>
        <x-common.form-actions form-id="team-categories-form" close-route="admin.team-categories.index" close-permission="team-categories.manage" submit-label="Save category" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="team-categories-form" method="POST" action="{{ route('admin.team-categories.update', $category) }}">
    @csrf
    @method('PUT')
    <x-common.form-actions form-id="team-categories-form" close-route="admin.team-categories.index" close-permission="team-categories.manage" submit-label="Save category" :sticky="true" />
    <x-common.component-card title="Team category details">
        @include('admin.pages.team-categories._form', ['submitLabel' => 'Save changes'])
    </x-common.component-card>
</form>
@endsection
