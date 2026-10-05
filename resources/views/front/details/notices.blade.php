<div class="notice-detail-page common-box pt-0" role="main">
    <div class="container">
        <div class="grid grid-cols-12 gap-6 lg:gap-8">
            <div class="col-span-12 min-w-0 lg:col-span-8">
                <div class="page-title">
                    <h1>{{ $item->title }}</h1>
                </div>
                <p class="mb-6 text-sm text-text_color">Published on {{ $item->published_at?->format('d M, Y') }}</p>
                @if($item->deadline_at)
                    <p class="mb-6 text-sm font-semibold text-text_color">Deadline: <time datetime="{{ $item->deadline_at->toIso8601String() }}">{{ $item->deadline_at->format('d M, Y H:i') }}</time></p>
                @endif
                @if($item->description)
                    <article class="mb-6">{!! $safeHtml->clean($item->description) !!}</article>
                @endif
                @if($item->fileMedia)
                    <x-front.file-reader :media="$item->fileMedia" :title="$item->title" />
                @else
                    <div class="resource-detail-page__empty">
                        <h2 class="mb-2 text-xl font-bold text-primary">File not available yet</h2>
                        <p>The file for this notice has not been uploaded. Please check back later.</p>
                    </div>
                @endif
                <a class="resource-detail-page__back" href="{{ route('public.notices.index') }}"><span class="icon-prev" aria-hidden="true"></span> Back to notices</a>
            </div>
            <aside class="col-span-12 min-w-0 lg:col-span-4" aria-labelledby="recent-notices-heading">
                <div class="rounded-md border-t-4 border-primary bg-dim_bg p-5 lg:sticky lg:top-25">
                    <h2 id="recent-notices-heading" class="mb-5 text-xl font-bold text-primary font-heading">Recently added notices</h2>
                    <ul class="divide-y divide-primary/20">
                        @forelse($recentNotices as $notice)
                            <li class="py-4 first:pt-0 last:pb-0">
                                <a class="block font-semibold text-primary hover:text-secondary" href="{{ route('public.notices.show', $notice->slug) }}">{{ $notice->title }}</a>
                                <time class="mt-2 block text-sm text-text_color" datetime="{{ $notice->published_at->toDateString() }}">{{ $notice->published_at->format('d M, Y') }}</time>
                                @if($notice->deadline_at)
                                    <time class="mt-1 block text-sm font-semibold text-text_color" datetime="{{ $notice->deadline_at->toIso8601String() }}">Deadline: {{ $notice->deadline_at->format('d M, Y H:i') }}</time>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-text_color">No other notices are available.</li>
                        @endforelse
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</div>
