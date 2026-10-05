<x-common.component-card title="Social links" desc="Add a platform name and URL. Empty URLs and # are hidden on the website.">
    <div x-data="{ rows: @js(array_values(old('social_links', $settings['social_links']) ?? [])) }">
        <input type="hidden" name="social_links_present" value="1">
        <div class="space-y-4">
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid items-end gap-4 rounded-lg border border-gray-200 p-4 dark:border-gray-800 md:grid-cols-[1fr_2fr_auto]">
                    <div><label :for="'social-label-' + index" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Platform / label</label><x-form.input name="social_label" x-bind:id="'social-label-' + index" x-bind:name="'social_links[' + index + '][label]'" x-model="row.label" maxlength="100" placeholder="Facebook" /></div>
                    <div><label :for="'social-url-' + index" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">URL</label><x-form.input name="social_url" x-bind:id="'social-url-' + index" x-bind:name="'social_links[' + index + '][url]'" x-model="row.url" maxlength="1000" placeholder="https://… or #" /></div>
                    <button type="button" @click="rows.splice(index, 1)" class="h-11 rounded-lg bg-error-50 px-4 text-sm font-medium text-error-600 dark:bg-error-500/10" :aria-label="'Remove social link ' + (index + 1)">Remove</button>
                </div>
            </template>
        </div>
        <button type="button" @click="rows.push({ label: '', url: '' })" :disabled="rows.length >= 20" class="mt-4 rounded-lg border border-brand-500 px-4 py-2.5 text-sm font-medium text-brand-500 disabled:opacity-50">Add social link</button>
    </div>
</x-common.component-card>
