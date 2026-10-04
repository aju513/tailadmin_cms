@if($content->exists && $content->galleryImages->isNotEmpty())
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($content->galleryImages as $image)
            <div class="space-y-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                @if($image->mediaAsset)
                    <img src="{{ $image->mediaAsset->url() }}" alt="{{ $image->mediaAsset->alt_text ?: $content->title }}" class="h-40 w-full rounded-lg object-cover">
                @endif
                <x-form.checkbox name="remove_gallery_ids[]" :id="'remove-homepage-image-'.$image->id" :value="$image->id" label="Remove image" :checked="in_array($image->id, old('remove_gallery_ids', []))" />
            </div>
        @endforeach
    </div>
@endif
<x-form.file-upload name="gallery_images[]" label="Add gallery images" upload-profile="images.homepage.gallery" :multiple="true" :max-files="config('settings.homepage.gallery_limit')" :help="'Up to '.config('settings.homepage.gallery_limit').' images. Images appear in upload order.'" :error="$errors->first('gallery_images') ?: $errors->first('gallery_images.*')" />
@error('remove_gallery_ids')<p class="text-sm text-error-600">{{ $message }}</p>@enderror
@error('remove_gallery_ids.*')<p class="text-sm text-error-600">{{ $message }}</p>@enderror
