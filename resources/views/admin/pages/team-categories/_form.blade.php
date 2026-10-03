<div class="space-y-6">
    <div class="grid gap-6 md:grid-cols-2">
        <x-form.input name="name" label="Category name" :value="old('name', $category->name)" required data-team-category-name />
        <div><x-form.input name="slug" label="URL slug" :value="old('slug', $category->slug)" data-team-category-slug /><p class="mt-1 text-xs text-gray-500">Generated from the category name.</p></div>
    </div>
    <x-form.editor name="description" label="Description" :value="old('description', $category->description)" placeholder="Write the category description..." />
    <x-form.toggle name="status" label="Active" :checked="old('status', $category->status ?? true)" />
    <div class="flex justify-end"><button class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white">{{ $submitLabel }}</button></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const name = document.querySelector('[data-team-category-name]');
    const slug = document.querySelector('[data-team-category-slug]');
    if (!name || !slug) return;
    const slugify = value => value.toString().toLowerCase().trim().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
    name.addEventListener('input', () => { slug.value = slugify(name.value); });
});
</script>
@endpush
