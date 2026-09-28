@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Team Member" />
<form method="POST" action="{{ route('admin.team-members.store') }}" enctype="multipart/form-data">
    @csrf
    <x-common.component-card title="Team member details" desc="Add a member’s name, role, photo, and biography.">
        @include('pages.admin.team-members._form', ['submitLabel' => 'Add team member'])
    </x-common.component-card>
</form>
@endsection
