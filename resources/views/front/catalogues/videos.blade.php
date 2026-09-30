@inject('embeds','App\Services\Frontend\VideoEmbedService')
<div class="video-page" role="main">
    <div class="container">
        <div class="page-title">
            <h1>
                {{ $heading }}
            </h1>
        </div>
        <div class="video-page__description">
            <div class="video-page__content lg:w-4/5 text-[15px] text-text_color">
{!! $safeHtml->clean($page->summary ?? '') !!}
</div>
        </div>
        <div class="mt-10 video-list__wrapper">
            <div class="grid grid-cols-12 gap-5">
@forelse($items as $item)
<div class="col-span-12 sm:col-span-6">
                    @if($embeds->url($item->video_url))<iframe
                        src="{{ $embeds->url($item->video_url) }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                        title="{{ $item->title }}"
                        frameborder="0"
                        width="100%"
                        height="350px"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>@else<a class="btn-primary" href="{{ $item->video_url }}" target="_blank" rel="noopener noreferrer">Watch video</a>@endif
                    <div class="rounded-bl-[5px] rounded-br-[5px] bg-dim_bg px-5 py-3">
                        <span class="block mb-1 text-lg font-bold text-text_color">
                            <a href="{{ route('public.videos.show',$item->slug) }}">{{ $item->title }}</a>
                        </span>
                        <div class="text-sm text-text_color">
{!! $safeHtml->clean($item->description) !!}
</div>
                    </div>
                </div>
@empty<p class="col-span-12 py-8 text-text_color">No videos published yet.</p>@endforelse
</div>
        </div>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
