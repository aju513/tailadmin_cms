<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
@isset($homepageContent)
    @if(filled($homepageContent->meta_keywords))<meta name="keywords" content="{{ $homepageContent->meta_keywords }}">@endif
@endisset
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach($seo['alternates'] as $language=>$url)<link rel="alternate" hreflang="{{ $language }}" href="{{ $url }}">@endforeach
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
<meta property="og:site_name" content="{{ $settings['site_name'] }}">
<meta name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if($seo['image'])<meta property="og:image" content="{{ $seo['image'] }}"><meta name="twitter:image" content="{{ $seo['image'] }}">@endif
<script type="application/ld+json">{!! $seo['schema'] !!}</script>
