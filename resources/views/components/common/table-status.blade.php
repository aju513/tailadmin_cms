@props(['id', 'status', 'label', 'permission', 'url', 'selectionKey', 'activeValue' => 'published', 'inactiveValue' => 'draft', 'activeLabel' => 'Unpublish', 'inactiveLabel' => 'Publish'])

@can($permission)
    <form method="POST" action="{{ $url }}" @submit.prevent="changeStatus($el.action, 'PATCH', [@js((string) $id)], statuses[@js((string) $id)] === @js($activeValue) ? @js($inactiveValue) : @js($activeValue))">
        @csrf @method('PATCH')
        <input type="hidden" name="{{ $selectionKey }}[]" value="{{ $id }}">
        <input type="hidden" name="status" value="{{ (string) $status === $activeValue ? $inactiveValue : $activeValue }}">
        <button type="submit" @dragstart.stop.prevent :disabled="statusBusy" :aria-busy="pendingIds.includes(@js((string) $id))"
            class="page-status-control inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full align-middle focus-visible:outline-none focus-visible:ring-4 disabled:cursor-wait disabled:opacity-50"
            :class="statuses[@js((string) $id)] === @js($activeValue) ? 'text-success-500 hover:bg-success-50 hover:text-success-600 focus-visible:ring-success-500/20 dark:hover:bg-success-500/10' : 'text-error-500 hover:bg-error-50 hover:text-error-600 focus-visible:ring-error-500/20 dark:hover:bg-error-500/10'"
            :title="pendingIds.includes(@js((string) $id)) ? 'Updating status…' : (statuses[@js((string) $id)] === @js($activeValue) ? @js($activeLabel) : @js($inactiveLabel))"
            :aria-label="(statuses[@js((string) $id)] === @js($activeValue) ? @js($activeLabel) : @js($inactiveLabel)) + ' ' + @js($label)">
            <span x-show="statuses[@js((string) $id)] === @js($activeValue)" style="{{ (string) $status === $activeValue ? '' : 'display: none' }}"><x-common.menu-icon name="activate" class="page-status-icon h-7 w-7" /></span>
            <span x-show="statuses[@js((string) $id)] !== @js($activeValue)" style="{{ (string) $status === $activeValue ? 'display: none' : '' }}"><x-common.menu-icon name="deactivate" class="page-status-icon h-7 w-7" /></span>
        </button>
    </form>
@else
    <span class="inline-flex h-8 w-8 items-center justify-center align-middle {{ (string) $status === $activeValue ? 'text-success-500' : 'text-error-500' }}">
        <x-common.menu-icon :name="(string) $status === $activeValue ? 'activate' : 'deactivate'" class="page-status-icon h-7 w-7" />
        <span class="sr-only">{{ $activeValue === 'published' ? ((string) $status === $activeValue ? 'Published' : 'Draft') : ((string) $status === $activeValue ? 'Active' : 'Inactive') }}</span>
    </span>
@endcan
