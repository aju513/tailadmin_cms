@props(['media', 'title'])
@php
    $fileUrl = url($media->url());
    $extension = strtolower(pathinfo($media->path, PATHINFO_EXTENSION));
    $isImage = str_starts_with($media->mime_type, 'image/');
    $isPdf = $media->mime_type === 'application/pdf' || $extension === 'pdf';
    $isOffice = in_array($extension, ['doc', 'docx', 'xls', 'xlsx']);
    $viewerUrl = $isOffice ? 'https://view.officeapps.live.com/op/embed.aspx?src='.rawurlencode($fileUrl) : $fileUrl;
@endphp
<section aria-label="{{ $title }} file reader">
    @if($isImage)
        <div class="resource-detail-page__viewer resource-detail-page__viewer--image">
            <img src="{{ $fileUrl }}" alt="{{ $title }}" />
        </div>
    @elseif($isPdf || $isOffice)
        <iframe class="resource-detail-page__viewer" src="{{ $viewerUrl }}" title="{{ $title }} file preview" loading="lazy" referrerpolicy="no-referrer">
            <p>Preview unavailable. <a href="{{ $fileUrl }}">Open the file</a>.</p>
        </iframe>
    @else
        <p class="rounded-md bg-dim_bg p-5 text-text_color">A preview is unavailable for this file format.</p>
    @endif
    <div class="mt-4 flex flex-wrap items-center gap-4">
        <a class="btn-primary hav-icon" href="{{ $fileUrl }}" download="{{ $media->original_name }}">
            <span class="icon-download" aria-hidden="true"></span>
            <span>Download file</span>
        </a>
        <a class="inline-block text-sm text-primary underline" href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer">Open file in new tab</a>
    </div>
</section>
