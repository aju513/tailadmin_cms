@props([
    'formId',
    'closeRoute' => null,
    'closePermission' => null,
    'submitLabel' => 'Save',
    'disabled' => false,
    'sticky' => false,
])

@if($sticky)
    <div x-data="stickyFormActions(@js($formId))" data-sticky-form-actions="{{ $formId }}" class="sticky top-20 z-30 -mx-4 h-0 sm:-mx-6">
        <div x-show="stickyActions" x-cloak x-transition.opacity.duration.150ms style="left: 0; right: 0; width: 100%;" class="absolute top-0 flex items-center border-b border-gray-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 sm:px-6">
@endif
            <div class="flex items-center justify-end {{ $sticky ? 'ml-auto gap-3' : 'gap-2' }}">
                @if($closeRoute && (!$closePermission || auth()->user()?->can($closePermission)))
                    <a href="{{ route($closeRoute) }}" class="inline-flex h-11 items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Close</a>
                @endif
                @if($sticky)
                    <x-ui.button type="submit" :form="$formId" :disabled="$disabled">{{ $submitLabel }}</x-ui.button>
                @else
                    <x-ui.button type="submit" :id="$formId.'-save'" :form="$formId" :disabled="$disabled">{{ $submitLabel }}</x-ui.button>
                @endif
            </div>
@if($sticky)
        </div>
    </div>
@endif
