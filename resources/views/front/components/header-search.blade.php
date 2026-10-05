<div class="websearch-wrap">
    <button type="button" class="search-btn" aria-label="Search the website" aria-expanded="false" aria-controls="{{ $searchId }}-panel">
        <span class="icon-search" aria-hidden="true"></span>
    </button>
    <div id="{{ $searchId }}-panel" class="search-box-elements" hidden>
        <form method="GET" action="{{ route('public.search') }}" role="search" aria-label="Search the website">
            @if(request()->filled('lang'))<input type="hidden" name="lang" value="{{ request('lang') }}">@endif
            <label for="{{ $searchId }}" class="sr-only">Search the website</label>
            <div class="website-search-field">
                <input id="{{ $searchId }}" type="search" name="q" value="{{ request()->routeIs('public.search') ? $term : '' }}" maxlength="100" placeholder="Search the website" required class="notranslate" />
                <button type="submit" class="website-search-submit" aria-label="Submit search"><span class="icon-search" aria-hidden="true"></span></button>
            </div>
        </form>
        <button type="button" class="search-close" aria-label="Close search"><span class="icon-close" aria-hidden="true"></span></button>
    </div>
</div>
