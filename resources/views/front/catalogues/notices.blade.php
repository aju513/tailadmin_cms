@php($documents = $items->getCollection()->all())
<div class="common-box common-page document-board-page pt-0" role="main">
    <div class="container">
        <div class="page-title">
            <h1>{{ $heading }}</h1>
        </div>
        <div class="mb-8 max-w-4xl">
            <p class="text-[15px] text-text_color">{{ strip_tags($page->summary ?? '') }}</p>
        </div>

        <div class="notices-page__table-wrap" role="region" aria-label="{{ $heading }} list" tabindex="0">
            <table class="notices-page__table">
                <thead>
                    <tr>
                        <th scope="col">S.N.</th>
                        <th scope="col">Notice</th>
                        <th scope="col">Category</th>
                        <th scope="col">Published date</th>
                        <th scope="col">Online view</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($documents === []): ?>
                        <tr>
                            <td class="notices-page__empty" colspan="5">
                                No {{ strtolower($heading) }} are currently available.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $index => $document): ?>
                            @php($viewerUrl = route('public.notices.show',$document->slug))
@php($canViewDocument = (bool)$document->fileMedia)
                            <tr>
                                <td class="notices-page__number">{{ ($items->firstItem() ?? 1) + $index }}</td>
                                <td class="notices-page__title">
                                    <?php if ($canViewDocument): ?>
                                        <a class="notices-page__document-title" href="{{ $viewerUrl }}">
                                            {{ $document->title }}
                                        </a>
                                    <?php else: ?>
                                        {{ $document->title }}
                                    <?php endif; ?>
                                </td>
                                <td><span class="notices-page__category">{{ $document->category?->name }}</span></td>
                                <td class="notices-page__date">{{ $document->published_at?->format('d M, Y') }}</td>
                                <td>
                                    <?php if ($canViewDocument): ?>
                                        <a class="notices-page__action" href="{{ $viewerUrl }}">
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
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
