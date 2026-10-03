<div class="space-y-5">
    @if($record->exists && $record->photos->isNotEmpty())
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($record->photos as $photo)
                <div class="space-y-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                    @if($photo->media)<img src="{{ $photo->media->url() }}" alt="{{ $record->title }}" class="h-40 w-full rounded-lg object-cover" />@endif
                    <x-form.checkbox name="remove_photo_ids[]" :id="'remove-photo-'.$photo->id" :value="$photo->id" label="Remove image" :checked="in_array($photo->id, old('remove_photo_ids', []))" />
                </div>
            @endforeach
        </div>
    @endif
    <x-form.file-upload name="new_photos[]" label="Gallery images" upload-profile="images.gallery_photo" :multiple="true" :maxFiles="30" :error="$errors->first('new_photos') ?: $errors->first('new_photos.*')" help="Upload multiple images. Up to 30 per upload and 100 per gallery." />
</div>
