<section class="video-page" aria-label="Video catalogue">
    <div class="container pb-12">
        <div class="page-title"><h1>{{ $heading }}</h1></div>
        @if(isset($page) && $page->summary)<div class="mb-6 text-text_color">{!! $safeHtml->clean($page->summary) !!}</div>@endif
        @include('front.components.catalogue-search', ['searchLabel' => 'Search videos'])
        <div class="mt-8 grid grid-cols-12 gap-5">
            @forelse($items as $item)
                <div class="col-span-12 sm:col-span-6 lg:col-span-4">@include('front.components.video_item', ['video' => $item, 'videoGroup' => 'video-catalogue'])</div>
            @empty
                <p class="col-span-12 py-8 text-text_color">{{ request()->filled('q') || request()->filled('search') ? 'No videos match your search.' : 'No videos published yet.' }}</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $items->links('front.components.pagination') }}</div>
        @if(isset($page) && $page->body)<article class="prose mt-8 max-w-none">{!! $safeHtml->clean($page->body) !!}</article>@endif
    </div>
</section>
