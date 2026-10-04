@extends('admin.layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Team Member">
    <x-slot:actions>
        <x-common.form-actions form-id="team-members-form" close-route="admin.team-members.index" close-permission="team-members.manage" submit-label="Save team member" />
    </x-slot:actions>
</x-common.page-breadcrumb>
<form id="team-members-form" method="POST" action="{{ route('admin.team-members.update', $member) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-common.form-actions form-id="team-members-form" close-route="admin.team-members.index" close-permission="team-members.manage" submit-label="Save team member" :sticky="true" />
    <x-common.component-card title="Team member details" desc="Update this member’s profile and active status.">
        @include('admin.pages.team-members._form', ['submitLabel' => 'Save changes'])
    </x-common.component-card>
</form>
@endsection
