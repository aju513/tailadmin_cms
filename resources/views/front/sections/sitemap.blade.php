@inject('urls','App\Services\Frontend\SeoService')
<div class="sitemap-page" role="main">
    <div class="container">
        <div class="page-title"><h1>{{ $heading }}</h1></div>
        <div class="sitemap-page__list">
            <div class="sitemap-page__col">
                <span class="sitemap-page__col-title">Website</span>
                <ul>@foreach(['public.home'=>'Home','public.news.index'=>'News','public.notices.index'=>'Notices','public.resources.index'=>'Resources','public.team.index'=>'Our Team','public.halls.index'=>'Our Halls','public.gallery.index'=>'Photo Gallery','public.videos.index'=>'Videos','public.contact'=>'Contact Us'] as $route=>$label)<li><a href="{{ route($route) }}">{{ $label }}</a></li>@endforeach</ul>
            </div>
            <div class="sitemap-page__col">
                <span class="sitemap-page__col-title">Pages</span>
                <ul>@foreach($pages as $entry)<li><a href="{{ $urls->recordPath('pages',$entry) }}">{{ $entry->title }}</a></li>@endforeach<li><a href="{{ route('public.sitemap.index') }}">XML sitemap</a></li></ul>
            </div>
        </div>
    </div>
</div>
