<x-common.component-card title="Resource category details">
    <div class="space-y-6">
        <x-form.input name="name" label="Category name" :value="$record->name" required maxlength="255" />
        <div class="grid gap-6 sm:grid-cols-2">
            <x-form.input name="slug" label="URL slug" :value="$record->slug" help="Leave blank to generate from the name." />
            <div class="flex items-center sm:pt-6">
                <x-form.toggle name="is_active" label="Published" :checked="$record->is_active ?? true" help="Unpublished categories and their resources are hidden from the website." />
            </div>
        </div>
        <div class="w-full min-w-0">
            <x-form.editor name="description" label="Description" :value="$record->description" placeholder="Write the category description..." />
        </div>
    </div>
</x-common.component-card>
