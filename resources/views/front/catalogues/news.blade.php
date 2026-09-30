<div class="news-page blog-page" role="main">
    <div class="container">
        <div class="page-title">
            <h1>{{ $heading }}</h1>
        </div>
        <div class="blog-page__description mt-6">
            <div class="w-full lg:w-4/5">
{!! $safeHtml->clean($page->summary ?? '') !!}
</div>
        </div>
    </div>

    <div class="news-list-wrapper common-box pt-0">
        <div class="container">
            <form class="mb-6 blog-list__sort" method="get" action="{{ url()->current() }}">
                <div class="flex flex-wrap gap-x-5 gap-y-3">
                    <div class="relative blog-list__sort-category md:w-67.5 max-sm:w-full">
                        <label class="sr-only" for="news-category">Filter by category</label>
                        <select
                            id="news-category"
                            name="category"
                            class="rounded-md border border-primary/20 w-full cursor-pointer appearance-none px-6.25 leading-6 text-text_color">
                            <option value="">All Categories</option>
                            @foreach($newsCategories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach
                        </select>
                    </div>

                    <div class="relative blog-list__sort-search md:w-67.5 max-sm:w-full">
                        <label class="sr-only" for="news-search">Search news</label>
                        <input
                            id="news-search"
                            type="search"
                            name="q"
                            value="{{ request('q', request('search')) }}"
                            placeholder="Search news"
                            class="border border-primary/20 rounded-md w-full pl-3.5 pr-12.5 py-3.75 text-[15px] leading-6 text-text_color placeholder:text-text_color" />
                        <button type="submit" class="absolute right-3 top-3.5" aria-label="Search news">
                            <span class="text-2xl icon-search text-secondary"></span>
                        </button>
                    </div>
                </div>
            </form>

            <h2 class="section-title homepage__section-title mb-6">Latest News</h2>
            <div class="homepage__news-grid grid grid-cols-12">
@forelse($items as $item)@include('front.components.news-card',['cardClass'=>'col-span-12 sm:col-span-6 lg:col-span-4'])@empty<p class="col-span-12 py-8 text-text_color">No news matches your search.</p>@endforelse
</div>
        </div>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
