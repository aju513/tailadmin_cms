@extends('admin.layouts.app')
@section('content')
<x-common.page-breadcrumb pageTitle="Grievance Details"><x-slot:actions>@can('grievances.manage')<a class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300" href="{{ route('admin.grievances.index') }}">Back to grievances</a>@endcan</x-slot:actions></x-common.page-breadcrumb>
<x-common.component-card :title="$grievance->reference" :desc="$grievance->subject ?: 'No subject'">
    <dl class="grid gap-5 text-sm md:grid-cols-2">
        @foreach(['Name' => $grievance->full_name ?: 'Anonymous', 'Email' => $grievance->email ?: 'Not provided', 'Phone' => $grievance->phone ?: 'Not provided', 'Submitted at' => $grievance->created_at->format('d M Y H:i'), 'Source page' => $grievance->page_title] as $label => $value)
            <div><dt class="mb-1 text-gray-500">{{ $label }}</dt><dd class="break-words font-medium text-gray-800 dark:text-white">{{ $value }}</dd></div>
        @endforeach
    </dl>
    <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-800"><h2 class="mb-3 font-medium text-gray-800 dark:text-white">Grievance message</h2><p class="whitespace-pre-wrap break-words text-sm text-gray-600 dark:text-gray-300">{{ $grievance->message }}</p></div>
    @if($grievance->attachment_path)<div class="mt-6"><a href="{{ route('admin.grievances.download', $grievance->id) }}" class="inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Download attachment</a><p class="mt-2 break-words text-xs text-gray-500">{{ $grievance->attachment_name }}</p></div>@endif
</x-common.component-card>
@endsection
