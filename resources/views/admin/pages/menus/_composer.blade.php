<aside class="min-w-0 rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" aria-label="Add links to {{ $menu->name }}">
    <div class="border-b border-gray-100 px-5 py-5 dark:border-gray-800">
        <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $menu->isImportantLinks() ? 'Add an external link' : 'Add to menu' }}</h3>
        <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $menu->isImportantLinks() ? 'Add a label and a full website URL.' : 'Choose website pages or create a custom link.' }}</p>
        @unless($menu->isImportantLinks())
            <div role="tablist" aria-label="Menu item source" class="mt-4 grid grid-cols-2 gap-1 rounded-lg bg-gray-100 p-1 dark:bg-gray-900">
                <button type="button" role="tab" id="menu-{{ $menu->id }}-pages-tab" aria-controls="menu-{{ $menu->id }}-pages-panel" :aria-selected="activePanel === 'pages'" :tabindex="activePanel === 'pages' ? 0 : -1" @click="activePanel = 'pages'" @keydown.arrow-right.prevent="activePanel = 'link'; $nextTick(() => $el.nextElementSibling.focus())" class="inline-flex items-center justify-center gap-2 rounded-md px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" :class="activePanel === 'pages' ? 'bg-white text-brand-500 shadow-theme-xs dark:bg-gray-800 dark:text-brand-400' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white'">
                    <x-common.menu-icon name="pages" class="h-4 w-4" />Pages
                </button>
                <button type="button" role="tab" id="menu-{{ $menu->id }}-link-tab" aria-controls="menu-{{ $menu->id }}-link-panel" :aria-selected="activePanel === 'link'" :tabindex="activePanel === 'link' ? 0 : -1" @click="activePanel = 'link'" @keydown.arrow-left.prevent="activePanel = 'pages'; $nextTick(() => $el.previousElementSibling.focus())" class="inline-flex items-center justify-center gap-2 rounded-md px-3 py-2.5 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" :class="activePanel === 'link' ? 'bg-white text-brand-500 shadow-theme-xs dark:bg-gray-800 dark:text-brand-400' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white'">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0m2 1 1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0" /></svg>Custom link
                </button>
            </div>
        @endunless
    </div>

    @unless($menu->isImportantLinks())
        <div id="menu-{{ $menu->id }}-pages-panel" role="tabpanel" aria-labelledby="menu-{{ $menu->id }}-pages-tab" x-show="activePanel === 'pages'" @if($initialPanel !== 'pages') style="display: none" @endif>
            <form method="POST" action="{{ route('admin.menus.assign') }}" class="space-y-5 p-5">
                @csrf
                <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                <x-form.multiselect name="page_ids[]" :id="'page-ids-'.$menu->id" label="Assign pages" :cascade="true" :options="$availablePages->get($menu->id, collect())->map(fn ($page) => ['value' => $page->id, 'parent' => $page->parent_id, 'label' => str_repeat('-- ', substr_count($page->path, '/')).$page->title.' ('.$page->path.')'])->all()" placeholder="Search and select pages" />
                <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                    <x-ui.button type="submit" size="sm" class="w-full" :disabled="$availablePages->get($menu->id, collect())->isEmpty()">
                        <x-common.menu-icon name="create" class="h-4 w-4" />Assign Menu
                    </x-ui.button>
                </div>
            </form>
        </div>
    @endunless

    <div id="menu-{{ $menu->id }}-link-panel" @unless($menu->isImportantLinks()) role="tabpanel" aria-labelledby="menu-{{ $menu->id }}-link-tab" x-show="activePanel === 'link'" @if($initialPanel !== 'link') style="display: none" @endif @endunless>
        <form method="POST" action="{{ route('admin.menus.links.store') }}" class="space-y-5 p-5">
            @csrf
            <input type="hidden" name="menu_id" value="{{ $menu->id }}">
            <x-form.input name="label" :id="'menu-'.$menu->id.'-label'" label="Link label" :placeholder="$menu->isImportantLinks() ? 'e.g. Ministry of Education' : 'e.g. Contact us'" maxlength="255" required />
            @if($menu->isImportantLinks())
                <x-form.input name="external_url" :id="'menu-'.$menu->id.'-url'" label="External URL" type="url" placeholder="https://example.com" maxlength="1000" required />
            @else
                <x-form.input name="external_url" :id="'menu-'.$menu->id.'-url'" label="Link URL" placeholder="/contact or https://example.com" maxlength="1000" required />
                <x-form.select name="parent_id" :id="'menu-'.$menu->id.'-parent'" label="Parent item" :options="[''=>'Top level'] + $menu->items->pluck('label','id')->all()" />
            @endif
            <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                <x-ui.button type="submit" size="sm" class="w-full"><x-common.menu-icon name="create" class="h-4 w-4" />Add link</x-ui.button>
            </div>
        </form>
    </div>
</aside>
