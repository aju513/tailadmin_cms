@inject('embeds','App\Services\Frontend\VideoEmbedService')
@php($embed = $embeds->url($video->video_url))
<article class="overflow-hidden rounded-lg border border-primary/15 bg-dim_bg">
    <a href="{{ $embed ?: $video->video_url }}" @if($embed) data-fancybox="{{ $videoGroup }}" data-type="iframe" data-caption="{{ e($video->title) }}" @else target="_blank" rel="noopener noreferrer" @endif class="relative block" aria-label="Play {{ $video->title }}">
        <x-front.image :media="$video->coverMedia" :alt="$video->title" width="600" height="340" class="aspect-video w-full object-cover" />
        <span class="absolute inset-0 flex items-center justify-center"><span class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-2xl text-white" aria-hidden="true">▶</span></span>
    </a>
    <div class="p-5"><h2 class="mb-2 text-lg font-bold text-primary"><a href="{{ route('public.videos.show', $video->slug) }}">{{ $video->title }}</a></h2>@if($videoDescription ?? true)<p class="text-sm text-text_color">{{ Str::limit(strip_tags($video->description ?? ''), 160) }}</p>@endif</div>
</article>
