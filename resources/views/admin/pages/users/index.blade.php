@extends('admin.layouts.app')

@section('content')
<div x-data="pageManager(@js($users->mapWithKeys(fn ($record) => [(string) $record->id => $record->status->value])->all()), 'users')">
    <x-common.page-breadcrumb pageTitle="Users">
        <x-slot:actions>
            @can('users.change-status')
                <form method="POST" action="{{ route('admin.users.bulk-status') }}" @submit.prevent="changeStatus($el.action, 'PATCH', [...selected], $event.submitter?.value)" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <template x-for="userId in selected" :key="`status-${userId}`"><input type="hidden" name="users[]" :value="userId"></template>
                    <button name="status" value="active" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-success-500/40 px-3 py-2.5 text-sm font-medium text-success-600 disabled:cursor-not-allowed disabled:opacity-40" title="Activate selected users"><x-common.menu-icon name="activate" class="h-4 w-4" />Activate</button>
                    <button name="status" value="inactive" type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg border border-warning-500/40 px-3 py-2.5 text-sm font-medium text-warning-600 disabled:cursor-not-allowed disabled:opacity-40" title="Deactivate selected users"><x-common.menu-icon name="deactivate" class="h-4 w-4" />Deactivate</button>
                </form>
            @endcan
            @can('users.delete')
                <form method="POST" action="{{ route('admin.users.bulk-destroy') }}" onsubmit="return confirm('Permanently delete the selected users?')" class="flex items-center">
                    @csrf
                    @method('DELETE')
                    <template x-for="userId in selected" :key="`delete-${userId}`"><input type="hidden" name="users[]" :value="userId"></template>
                    <button type="submit" :disabled="selected.length === 0 || statusBusy" class="inline-flex items-center gap-1.5 rounded-lg bg-error-50 px-3 py-2.5 text-sm font-medium text-error-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-error-500/10" title="Delete selected users"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button>
                </form>
            @endcan
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600"><x-common.menu-icon name="create" class="h-4 w-4" />Create user</a>
            @endcan
        </x-slot:actions>
    </x-common.page-breadcrumb>
    <x-common.table-status-feedback />
    <x-common.component-card title="User management" desc="Create accounts, assign roles, and control account access.">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-3 sm:grid-cols-[1fr_180px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Search name or email" class="h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white">
            <select name="status" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
            <button class="rounded-lg border border-gray-300 px-4 text-sm font-medium dark:border-gray-700 dark:text-white">Filter</button>
        </form>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead><tr class="text-left text-xs uppercase text-gray-500"><th class="px-2 py-3">S.N.</th><th class="px-2 py-3">Status</th><th class="px-2 py-3"><x-common.table-select-all aria-label="Select all users" /></th><th class="px-4 py-3">User</th><th class="px-4 py-3">Roles</th><th class="px-4 py-3 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($users as $user)
                    <tr @can('users.edit') onclick="if (!event.target.closest('a,button,form,input,select,textarea,label')) window.location.href='{{ route('admin.users.edit', $user) }}'" title="Open {{ $user->name }} for editing" @endcan class="group transition @can('users.edit') cursor-pointer hover:bg-brand-50/40 dark:hover:bg-brand-500/5 @else hover:bg-gray-50 dark:hover:bg-white/[0.02] @endcan">
                        <td class="px-2 py-4 text-sm text-gray-500">{{ $users->firstItem() + $loop->index }}</td>
                        <td class="px-2 py-4 text-center" @mousedown.stop><x-common.table-status :id="$user->id" :status="$user->status->value" :label="$user->name" permission="users.change-status" :url="route('admin.users.bulk-status')" selection-key="users" active-value="active" inactive-value="inactive" active-label="Deactivate" inactive-label="Activate" /></td>
                        <td class="px-2 py-4"><x-common.table-checkbox value="{{ $user->id }}" x-model="selected" aria-label="Select {{ $user->name }}" @dragstart.stop.prevent /></td><td class="px-4 py-4"><p class="font-medium text-gray-800 dark:text-white">{{ $user->name }}</p><p class="text-sm text-gray-500">{{ $user->email }}</p></td>
                        <td class="px-4 py-4"><div class="flex flex-wrap gap-1">@foreach($user->roles as $role)<x-ui.badge color="primary">{{ $role->name }}</x-ui.badge>@endforeach</div></td>
                        <td class="px-4 py-4"><div class="flex justify-end gap-2">
                            @can('users.show')<a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-500 transition hover:border-brand-500 hover:bg-brand-50 dark:border-gray-700 dark:hover:bg-brand-500/10" title="View user"><x-common.menu-icon name="view" class="h-4 w-4" />View</a>@endcan
                            @can('users.edit')<a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-brand-600 transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:border-gray-700 dark:text-white dark:hover:bg-brand-500/10" title="Edit user"><x-common.menu-icon name="edit" class="h-4 w-4" />Edit</a>@endcan
                            @can('users.delete')<form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Permanently delete this user?')">@csrf @method('DELETE')<button class="inline-flex items-center gap-1 rounded-lg bg-error-50 px-3 py-2 text-xs font-medium text-error-600 transition hover:bg-error-100 hover:text-error-700 dark:bg-error-500/10 dark:hover:bg-error-500/20" title="Delete user"><x-common.menu-icon name="delete" class="h-4 w-4" />Delete</button></form>@endcan
                        </div></td></tr>
                @empty<tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No users found.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    </x-common.component-card>
</div>
@endsection
