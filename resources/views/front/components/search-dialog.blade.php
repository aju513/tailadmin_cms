<dialog id="site-search-dialog" class="site-search-dialog notranslate" aria-labelledby="site-search-title">
    <div class="site-search-panel">
        <button type="button" class="site-search-close" aria-label="Close search"><span class="icon-close" aria-hidden="true"></span></button>
        <h2 id="site-search-title">Search</h2>
        <form method="GET" action="{{ route('public.search') }}" class="site-search-form" role="search" aria-label="Search the website">
            @if(request()->filled('lang'))<input type="hidden" name="lang" value="{{ request('lang') }}">@endif
            <label for="site-search-input" class="sr-only">Search the website</label>
            <input id="site-search-input" type="search" name="q" value="{{ request()->routeIs('public.search') ? $term : '' }}" maxlength="100" placeholder="What are you looking for?" autocomplete="off" required />
            <button type="submit" aria-label="Submit search"><span class="icon-search" aria-hidden="true"></span></button>
        </form>
        <p class="site-search-hint">Search trainings, resources, notices, and more.</p>
    </div>
</dialog>
