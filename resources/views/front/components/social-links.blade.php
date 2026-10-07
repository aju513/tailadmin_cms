@if($socialLinks)
    <div class="footer__social flex flex-wrap gap-2" aria-label="Social links">
        @foreach($socialLinks as $link)
            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#c8e7f6] text-primary transition hover:bg-primary hover:text-white">
                <span class="{{ $link['icon'] }}" aria-hidden="true"></span>
            </a>
        @endforeach
    </div>
@endif
