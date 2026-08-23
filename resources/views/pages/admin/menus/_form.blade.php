<div class="grid gap-6 md:grid-cols-2">
    <x-form.select name="menu_id" label="Menu position" required>
        <option value="">Choose a menu position</option>
        @foreach($menus->groupBy(fn ($menu) => match ($menu->location) { 'header' => 'Header Menu', 'footer' => 'Footer Menu', default => 'Dynamic Menus' }) as $group => $groupMenus)
            <optgroup label="{{ $group }}">
                @foreach($groupMenus as $menu)
                    <option value="{{ $menu->id }}" @selected((string) old('menu_id', $item?->menu_id) === (string) $menu->id)>{{ $menu->name }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </x-form.select>
    <x-form.input name="label" label="Label" :value="old('label', $item?->label)" required />
    <x-form.select name="page_id" label="Page"><option value="">External URL instead</option>@foreach($pages as $page)<option value="{{ $page->id }}" @selected((string) old('page_id', $item?->page_id) === (string) $page->id)>{{ $page->title }} ({{ $page->path }})</option>@endforeach</x-form.select>
    <x-form.input name="external_url" label="External URL" :value="old('external_url', $item?->external_url)" />
    <x-form.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $item?->sort_order ?? 0)" />
    <x-form.toggle name="is_visible" label="Visible" :checked="old('is_visible', $item?->is_visible ?? true)" />
</div>
<div class="flex justify-end"><button class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/30">{{ $submitLabel }}</button></div>
