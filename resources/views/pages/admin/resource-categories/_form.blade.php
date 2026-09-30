<x-common.component-card title="Resource category details">
    <div class="grid gap-6 sm:grid-cols-2">
        <x-form.input name="name" label="Category name" :value="$record->name" required maxlength="255" />
        <x-form.input name="slug" label="URL slug" :value="$record->slug" help="Leave blank to generate from the name." />
        <x-form.input name="sort_order" label="Display order" type="number" :value="$record->sort_order ?? 0" min="0" required />
        <div class="sm:col-span-2"><x-form.textarea name="description" label="Description" :value="$record->description" maxlength="5000" rows="4" /></div>
    </div>
</x-common.component-card>
