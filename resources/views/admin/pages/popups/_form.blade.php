<x-common.component-card title="Popup details">
    <x-form.input name="title" label="Title" :value="$popup->title" maxlength="255" required />
    <x-form.textarea name="message" label="Message (optional)" :value="$popup->message" maxlength="5000" help="Plain text only. Line breaks are preserved." />
    <div class="grid gap-4 sm:grid-cols-2">
        <x-form.input name="button_label" label="Button label (optional)" :value="$popup->button_label" maxlength="100" />
        <x-form.input name="button_url" label="Button URL (optional)" type="url" :value="$popup->button_url" maxlength="2048" help="Include http:// or https://. Supply a label and URL together." />
    </div>
    @can('popups.publish')
        <x-form.toggle name="status" label="Published" on-value="published" off-value="draft" :checked="old('status', $popup->status?->value ?? 'draft') === 'published'" />
    @else
        <input type="hidden" name="status" value="{{ $popup->status?->value ?? 'draft' }}">
        <p class="text-sm text-gray-500">Publication requires publish permission.</p>
    @endcan
</x-common.component-card>
<x-common.component-card title="Popup image">
    @if($popup->media)<img src="{{ $popup->media->url() }}" alt="{{ $popup->alt_text ?: $popup->title }}" class="max-h-64 max-w-full rounded-xl object-contain" />@endif
    <x-form.file-upload name="image" label="Image" upload-profile="images.popup" :required="! $popup->media" help="Portrait and landscape images are supported. Upload a new image to replace the current one." />
    <x-form.input name="alt_text" label="Image description (optional)" :value="$popup->alt_text" maxlength="255" />
</x-common.component-card>
