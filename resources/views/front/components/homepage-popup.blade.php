@if($homepagePopup)
<dialog id="homepage-popup" class="homepage-popup notranslate" aria-labelledby="homepage-popup-title">
    <div class="homepage-popup__panel">
        <button type="button" data-popup-close class="homepage-popup__close" aria-label="Close announcement"><span class="icon-close" aria-hidden="true"></span></button>
        <h2 id="homepage-popup-title">{{ $homepagePopup->title }}</h2>
        <img src="{{ $homepagePopup->media->url() }}" alt="{{ $homepagePopup->alt_text ?: $homepagePopup->title }}" decoding="async">
        @if(filled($homepagePopup->message))<p class="homepage-popup__message">{{ $homepagePopup->message }}</p>@endif
        @if($homepagePopup->button_url)<a href="{{ $homepagePopup->button_url }}" class="btn-venue btn-venue--compact"><span class="btn-venue__text">{{ $homepagePopup->button_label }}</span><span class="btn-venue__icon icon-arrow-up-right" aria-hidden="true"></span></a>@endif
    </div>
</dialog>
@endif
