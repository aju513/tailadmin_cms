<label class="table-checkbox relative inline-flex h-8 w-8 cursor-pointer items-center justify-center align-middle">
    <input type="checkbox" {{ $attributes->class(['absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0']) }}>
    <span class="table-checkbox-box pointer-events-none inline-flex h-[22px] w-[22px] items-center justify-center rounded-md border-2 border-gray-300 bg-white text-white dark:border-gray-600 dark:bg-gray-900" aria-hidden="true">
        <svg class="table-checkbox-tick absolute h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="m4 10 4 4 8-8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
        <svg class="table-checkbox-mixed absolute h-4 w-4" viewBox="0 0 20 20" fill="none"><path d="M5 10h10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" /></svg>
    </span>
</label>
