@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Create Team Category">
    <x-slot:actions>
        <x-common.form-actions form-id="team-categories-form" close-route="admin.team-categories.index" close-permission="team-categories.manage" submit-label="Save category" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="team-categories-form" method="POST" action="{{ route('admin.team-categories.store') }}">
    @csrf
    <x-common.form-actions form-id="team-categories-form" close-route="admin.team-categories.index" close-permission="team-categories.manage" submit-label="Save category" :sticky="true" />
    <x-common.component-card title="Team category details">
        @include('admin.pages.team-categories._form', ['submitLabel' => 'Create category'])
    </x-common.component-card>
</form>
@endsection
