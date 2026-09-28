@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Team Member" />
<form method="POST" action="{{ route('admin.team-members.update', $member) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-common.component-card title="Team member details" desc="Update this member’s profile and active status.">
        @include('pages.admin.team-members._form', ['submitLabel' => 'Save changes'])
    </x-common.component-card>
</form>
@endsection
