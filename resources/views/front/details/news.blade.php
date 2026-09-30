<div class="news-detail-page blog-detail-page blog-page common-box pt-0" role="main">
    <div class="container">
        <div class="page-title">
            <h1>{{ $item->title }}</h1>
        </div>

        <div class="blog-author-share">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 blog-author">
                    <div class="blog-author__image flex-[0_0_55px]">
                        <div class="placeholder__img-wrapper">
                            <div class="w-full placeholder__img">
                                <img
                                    src="/front/images/svg/organization.svg"
                                    width="70"
                                    height="70"
                                    class="rounded-full"
                                    alt="LRTI Communications" />
                            </div>
                        </div>
                    </div>
                    <div class="blog-author__content">
                        <div class="text-lg font-semibold text-primary font-heading">{{ $item->author?->name ?? $settings['site_name'] }}</div>
                        <div class="text-sm text-text_color opacity-80">Updated on {{ $item->published_at?->format('d M, Y') }}</div>
                    </div>
                </div>
                <div class="blog-share">
                    <div class="share-wrap" id="newsShareDropdown">
                        <button id="newsShareToggle" type="button" class="flex items-center gap-1 share-trigger-btn group" aria-expanded="false">
                            <div class="share-icon">
                                <span class="text-base text-primary icon-share group-hover:text-secondary"></span>
                            </div>
                            <span class="text-primary group-hover:text-secondary">Share</span>
                        </button>
                        <div class="rounded-md share-list" id="newsShareMenu">
                            <button type="button" data-share-network="facebook" class="share-list__item share-facebook flex items-center gap-1 px-3 py-2 text-[17px] font-semibold">
                                <span class="icon-facebook"></span><span class="ml-2 text-sm">Facebook</span>
                            </button>
                            <button type="button" data-share-network="twitter" class="share-list__item share-x flex items-center gap-1 px-3 py-2 text-[17px] font-semibold">
                                <span class="icon-x"></span><span class="ml-2 text-sm">X</span>
                            </button>
                            <button type="button" data-share-network="linkedin" class="share-list__item share-linkedin flex items-center gap-1 px-3 py-2 text-[17px] font-semibold">
                                <span class="icon-linkedin"></span><span class="ml-2 text-sm">LinkedIn</span>
                            </button>
                            <button type="button" data-share-network="whatsapp" class="share-list__item share-whatsapp flex items-center gap-1 px-3 py-2 text-[17px] font-semibold">
                                <span class="icon-whatsapp"></span><span class="ml-2 text-sm">WhatsApp</span>
                            </button>
                            <button type="button" data-share-network="copy" class="share-list__item share-copy flex items-center gap-1 px-3 py-2 text-[17px] font-semibold">
                                <span class="icon-copylink text-xl"></span><span class="ml-2 text-sm">Copy</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 news-content blog-content">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 lg:col-span-8">
                    <article class="js-toc-content">
@if($item->subtitle)<p>{{ $item->subtitle }}</p>@endif
{!! $safeHtml->clean($item->body) !!}
</article>

                    <hr class="mt-8 border-t border-dashed border-primary/20" />
                    <div class="flex flex-wrap gap-1 mt-8 mb-8 blog__category-list">
@foreach($item->tags as $tag)<a class="blog__category-list-item" href="{{ route('public.news.tag',$tag->slug) }}">{{ $tag->name }}</a>@endforeach
</div>

                    <div class="mt-5 rounded-[5px] bg-[#e6f6ff] px-4 py-8 text-center">
                        <span class="block text-text_color font-heading">Contact {{ $settings['site_name'] }} for more information</span>
                        <span class="block text-lg font-bold text-secondary">
                            <a href="mailto:{{ $settings['email'] }}" class="inline-block px-2 transition-all duration-500 text-secondary hover:text-primary">{{ $settings['email'] }}</a>
                            or
                            <a href="tel:{{ $settings['phone'] }}" class="inline-block px-2 transition-all duration-500 text-secondary hover:text-primary">{{ $settings['phone'] }}</a>
                        </span>
                    </div>

                    
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <aside class="sticky top-25" id="sidebar-toc">
                        <div id="toggleButton">
                            <span class="show-icon"><span class="text-2xl icon-toc"></span></span>
                            <span class="close-icon"><span class="text-xl icon-close text-primary"></span></span>
                        </div>
                        <div class="toc-list-wrapper mb-6 rounded-r-[5px] border-t-4 border-primary bg-dim_bg p-4 px-6 py-5 shadow">
                            <span class="block mb-4 text-xl font-bold text-primary font-heading">Table of Contents</span>
                            <span class="pb-3 mb-5 text-lg border-b border-dashed toc md:text-xl"></span>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>
<section class="homepage__news scroll-wrap common-box pb-0" aria-labelledby="more-news-title">
    <div class="container-fluid">
        <div class="container">
            <div class="flex items-center justify-between mb-4">
                <h2 id="more-news-title" class="section-title homepage__section-title">More News</h2>
            </div>
            <div class="homepage__news-grid grid grid-cols-12">
@foreach($relatedNews as $relatedItem)@include('front.components.news-card',['item'=>$relatedItem,'cardClass'=>'col-span-6'])@endforeach
</div>
        </div>
    </div>
</section>
