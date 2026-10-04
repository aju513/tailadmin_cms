<section class="homepage__news scroll-wrap common-box pb-0" aria-labelledby="homepage-news-title">
    <div class="container-fluid">
        <div class="container max-md:!px-0 hav-title-btn">
            <div class="flex items-center justify-between mb-4">
                <h2 id="homepage-news-title" class="section-title homepage__section-title">Latest News</h2>
                <div class="section-title-btn">
                    <a href="{{ route('public.news.index') }}" class="btn-outline-secondary hav-icon group shrink-0 whitespace-nowrap">
                        See All News
                        <span
                            class="inline-block ml-1 text-base transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1"></span>
                    </a>
                </div>
            </div>
            <div class="homepage__news-grid grid grid-cols-12">
@foreach($latestNews as $item)@include('front.components.news_item',['cardClass'=>'col-span-4'])@endforeach
</div>
        </div>
    </div>
</section>
