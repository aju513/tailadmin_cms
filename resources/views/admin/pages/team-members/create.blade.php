@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Team Member">
    <x-slot:actions>
        <x-common.form-actions form-id="team-members-form" close-route="admin.team-members.index" close-permission="team-members.manage" submit-label="Save team member" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="team-members-form" method="POST" action="{{ route('admin.team-members.store') }}" enctype="multipart/form-data">
    @csrf
    <x-common.form-actions form-id="team-members-form" close-route="admin.team-members.index" close-permission="team-members.manage" submit-label="Save team member" :sticky="true" />
    <x-common.component-card title="Team member details" desc="Add a member’s name, role, photo, and biography.">
        @include('admin.pages.team-members._form', ['submitLabel' => 'Add team member'])
    </x-common.component-card>
</form>
@endsection
