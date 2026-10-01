@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Edit Hall">
    <x-slot:actions><x-ui.button type="submit" form="hall-form" data-hall-save>Save changes</x-ui.button></x-slot:actions>
</x-common.page-breadcrumb>
<form id="hall-form" method="POST" action="{{ route('admin.halls.update', $hall) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <x-common.component-card title="">
        @include('pages.admin.halls._form')
    </x-common.component-card>
</form>
@endsection
