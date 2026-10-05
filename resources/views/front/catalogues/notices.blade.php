@php($documents = $items->getCollection()->all())
<div class="common-box common-page document-board-page pt-0" role="main">
    <div class="container">
        <div class="page-title">
            <h1>{{ $heading }}</h1>
        </div>
        <div class="mb-8 max-w-4xl">
            <p class="text-[15px] text-text_color">{{ strip_tags($page->summary ?? '') }}</p>
        </div>

        <form class="mb-6" method="get" action="{{ url()->current() }}" role="search" aria-label="Search notices">
            @foreach(['notice_category_id', 'lang'] as $filter)
                @if(request()->filled($filter))<input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">@endif
            @endforeach
            <div class="relative w-full sm:max-w-sm">
                <label class="sr-only" for="notice-search">Search notices</label>
                <input id="notice-search" type="search" name="q" value="{{ request('q', request('search')) }}" maxlength="100" placeholder="Search notices" class="w-full rounded-md border border-primary/20 py-3.75 pl-3.5 pr-12.5 text-[15px] leading-6 text-text_color placeholder:text-text_color" />
                <button type="submit" class="absolute right-3 top-3.5" aria-label="Search notices"><span class="icon-search text-2xl text-secondary" aria-hidden="true"></span></button>
            </div>
            @if(request()->filled('q') || request()->filled('search'))
                <a href="{{ url()->current() }}{{ request()->only(['notice_category_id', 'lang']) ? '?'.http_build_query(request()->only(['notice_category_id', 'lang'])) : '' }}" class="mt-2 inline-block text-sm text-primary underline">Clear search</a>
            @endif
        </form>

        <div class="notices-page__table-wrap" role="region" aria-label="{{ $heading }} list" tabindex="0">
            <table class="notices-page__table">
                <thead>
                    <tr>
                        <th scope="col">S.N.</th>
                        <th scope="col">Notice</th>
                        <th scope="col">Category</th>
                        <th scope="col">Published date</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Online view</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($documents === []): ?>
                        <tr>
                            <td class="notices-page__empty" colspan="6">
                                {{ request()->filled('q') || request()->filled('search') ? 'No notices match your search.' : 'No '.strtolower($heading).' are currently available.' }}
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $index => $document): ?>
                            @php($viewerUrl = route('public.notices.show',$document->slug))
@php($canViewDocument = (bool)$document->fileMedia)
                            <tr class="relative cursor-pointer">
                                <td class="notices-page__number">{{ ($items->firstItem() ?? 1) + $index }}</td>
                                <td class="notices-page__title">
                                    <a class="notices-page__document-title after:absolute after:inset-0 after:content-[''] focus-visible:outline-none focus-visible:after:ring-2 focus-visible:after:ring-inset focus-visible:after:ring-primary" href="{{ $viewerUrl }}">
                                        {{ $document->title }}
                                    </a>
                                </td>
                                <td><span class="notices-page__category">{{ $document->category?->name }}</span></td>
                                <td class="notices-page__date">{{ $document->published_at?->format('d M, Y') }}</td>
                                <td class="notices-page__date">
                                    @if($document->deadline_at)
                                        <time datetime="{{ $document->deadline_at->toIso8601String() }}">{{ $document->deadline_at->format('d M, Y H:i') }}</time>
                                    @else
                                        <span aria-label="No deadline">—</span>
                                    @endif
                                </td>
                                <td>
                                    <?php if ($canViewDocument): ?>
                                        <a class="notices-page__action relative z-10" href="{{ $viewerUrl }}">
                                            View {{ $document->fileMedia?->mime_type === 'application/pdf' ? 'PDF' : 'file' }}
                                            <span class="icon-arrow-up-right" aria-hidden="true"></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-sm text-text_color/55">File unavailable</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        {{ $items->links('front.components.pagination') }}
    </div>
</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
