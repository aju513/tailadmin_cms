<article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
    <a href="{{ route('public.news.show', $item->slug) }}" class="block">
        @if($item->thumbnailMedia)
            <img src="{{ $item->thumbnailMedia->url() }}" alt="{{ $item->thumbnailMedia->alt_text ?: $item->title }}" class="h-52 w-full object-cover">
        @else
            <div class="flex h-52 items-center justify-center bg-gray-100 text-sm text-gray-400">News</div>
        @endif
    </a>
    <div class="space-y-3 p-5">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-gray-500">
            <time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->format('M d, Y') }}</time>
            @if($item->category)<span>·</span><a href="{{ route('public.news.category', $item->category->slug) }}" class="text-brand-600 hover:underline">{{ $item->category->name }}</a>@endif
        </div>
        <h3 class="text-lg font-semibold leading-snug text-gray-900"><a href="{{ route('public.news.show', $item->slug) }}" class="hover:text-brand-600">{{ $item->title }}</a></h3>
        @if($item->excerpt)<p class="text-sm leading-6 text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($item->excerpt), 150) }}</p>@endif
        <a href="{{ route('public.news.show', $item->slug) }}" class="inline-flex text-sm font-medium text-brand-600 hover:underline">Continue reading →</a>
    </div>
</article>
