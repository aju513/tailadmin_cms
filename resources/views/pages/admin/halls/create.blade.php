@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Add Hall">
    <x-slot:actions><x-ui.button type="submit" form="hall-form" data-hall-save>Save</x-ui.button></x-slot:actions>
</x-common.page-breadcrumb>
<form id="hall-form" method="POST" action="{{ route('admin.halls.store') }}" enctype="multipart/form-data">
    @csrf
    <x-common.component-card title="">
        @include('pages.admin.halls._form')
    </x-common.component-card>
</form>
@endsection
