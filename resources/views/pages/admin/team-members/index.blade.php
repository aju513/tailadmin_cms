@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Team Members">
    <x-slot:actions>
        @can('team-members.create')
            <a href="{{ route('admin.team-members.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Add team member</a>
        @endcan
    </x-slot:actions>
</x-common.page-breadcrumb>

<x-common.component-card title="Team member directory" desc="Add and manage the people featured as part of your team.">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-4 py-3">Member</th><th class="px-4 py-3">Category / Designation</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Created</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($members as $member)
                    <tr @can('team-members.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.team-members.edit', $member) }}'" title="Open {{ $member->name }} for editing" @endcan class="group transition @can('team-members.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                @if($member->photoMedia)
                                    <img src="{{ $member->photoMedia->url() }}" alt="{{ $member->photoMedia->alt_text ?: $member->name }}" class="h-11 w-11 rounded-full object-cover">
                                @else
                                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500 dark:bg-gray-800">{{ str($member->name)->substr(0, 1)->upper() }}</span>
                                @endif
                                <span class="font-medium text-gray-800 dark:text-white">{{ $member->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $member->category?->name ?? 'Uncategorized' }}<br><span class="text-xs text-gray-500">{{ $member->designation }}</span></td>
                        <td class="px-4 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $member->is_active ? 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $member->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-4"><div class="flex justify-end gap-2">
                            @can('team-members.edit')<a href="{{ route('admin.team-members.edit', $member) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 dark:border-gray-700 dark:text-brand-400 dark:hover:bg-brand-500/10"><x-common.menu-icon name="edit" class="h-4 w-4" />Edit</a>@endcan
                            @can('team-members.delete')<form method="POST" action="{{ route('admin.team-members.destroy', $member) }}" onsubmit="return confirm('Delete this team member?')">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 dark:bg-error-500/10 dark:hover:bg-error-500/20"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button></form>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500">No team members found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $members->links() }}
</x-common.component-card>
@endsection
