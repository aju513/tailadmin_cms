<div class="grid gap-6 md:grid-cols-2">
    <x-form.input name="name" label="Member name" :value="old('name', $member->name)" required />
    <x-form.input name="designation" label="Designation" :value="old('designation', $member->designation)" required />
    <x-form.select name="category_id" label="Team category" :options="$categories" :value="$member->category_id" />
    <x-form.input name="email" label="Public email" type="email" :value="old('email', $member->email)" />
    <x-form.input name="phone" label="Public phone" :value="old('phone', $member->phone)" />
    <x-form.toggle name="is_active" label="Active" :checked="old('is_active', $member->is_active)" />

    <div class="space-y-3 md:col-span-2">
        @if($member->photoMedia)
            <img src="{{ $member->photoMedia->url() }}" alt="{{ $member->photoMedia->alt_text ?: $member->name }}" class="h-28 w-28 rounded-xl object-cover">
        @else
            <div class="flex h-28 w-28 items-center justify-center rounded-xl bg-gray-100 text-3xl font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400" aria-label="No member photo">{{ str($member->name ?: 'Member')->substr(0, 1)->upper() }}</div>
        @endif
        <x-form.file-upload name="photo" label="Member photo" upload-profile="images.team_member" />
        <x-form.input name="photo_alt_text" label="Photo alt text" :value="old('photo_alt_text', $member->photoMedia?->alt_text)" />
    </div>

    <div class="md:col-span-2">
        <x-form.editor name="bio" label="Biography / team details" :value="old('bio', $member->bio)" placeholder="Write the team member biography..." />
    </div>
</div>

<div class="flex justify-end">
    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
</div>
