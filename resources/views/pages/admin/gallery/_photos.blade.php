<x-common.component-card title="Album photos" desc="Edit captions and display order, or remove photos from this album.">
    @if($record->exists && $record->photos->isNotEmpty())
        <div class="mb-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($record->photos as $index => $photo)
                @php
                    $previousPhoto = collect(old('photos', []))->first(fn ($item) => (string) ($item['id'] ?? '') === (string) $photo->id);
                @endphp
                <div class="space-y-4 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    @if($photo->media)<img src="{{ $photo->media->url() }}" alt="{{ $photo->caption ?? $record->title }}" class="h-40 w-full rounded-lg object-cover" />@endif
                    <input type="hidden" name="photos[{{ $index }}][id]" value="{{ $photo->id }}" />
                    <x-form.input :name="'photos['.$index.'][caption]'" label="Caption" :value="$previousPhoto ? ($previousPhoto['caption'] ?? '') : $photo->caption" :error="$errors->first('photos.'.$index.'.caption')" maxlength="500" />
                    <x-form.input :name="'photos['.$index.'][sort_order]'" label="Display order" type="number" :value="$previousPhoto['sort_order'] ?? $photo->sort_order" :error="$errors->first('photos.'.$index.'.sort_order')" min="0" required />
                    <x-form.checkbox name="remove_photo_ids[]" :id="'remove-photo-'.$photo->id" :value="$photo->id" label="Remove from album" :checked="in_array($photo->id, old('remove_photo_ids', []))" />
                </div>
            @endforeach
        </div>
    @endif
    <x-form.file-upload name="new_photos[]" label="Add photos" accept="image/jpeg,image/png,image/webp" :multiple="true" :maxFiles="30" :maxSize="5 * 1024 * 1024" :error="$errors->first('new_photos')" help="Up to 30 photos per upload and 100 per album. Maximum 5 MB each. After saving, captions and order can be edited on this page." />
</x-common.component-card>
