<div class="space-y-6">
    <x-form.input name="name" label="Category name" :value="old('name', $category->name)" required />
    <x-form.toggle name="status" label="Active" :checked="old('status', $category->status ?? true)" />
    <x-form.editor name="description" label="Description" :value="old('description', $category->description)" placeholder="Write the category description..." />
    <div class="flex justify-end"><button class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white">{{ $submitLabel }}</button></div>
</div>
