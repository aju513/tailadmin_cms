<form method="GET" action="{{ url()->current() }}" role="search" aria-label="{{ $searchLabel }}" class="mb-6 flex flex-wrap items-center gap-3">
    @if(request()->filled('lang'))<input type="hidden" name="lang" value="{{ request('lang') }}">@endif
    <div class="website-search-field sm:max-w-md">
        <label for="catalogue-query" class="sr-only">{{ $searchLabel }}</label>
        <input id="catalogue-query" type="search" name="q" value="{{ $term ?? request('q', request('search')) }}" maxlength="100" placeholder="{{ $searchLabel }}" class="notranslate w-full rounded-lg border border-primary/20 px-4 py-3 pr-12 text-text_color focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
        <button type="submit" class="website-search-submit" aria-label="Search"><span class="icon-search" aria-hidden="true"></span></button>
    </div>
    @if(request()->filled('q') || request()->filled('search'))<a class="text-sm text-primary underline" href="{{ url()->current() }}{{ request()->filled('lang') ? '?'.http_build_query(['lang'=>request('lang')]) : '' }}">Clear search</a>@endif
</form>
