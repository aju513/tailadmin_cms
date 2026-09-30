@extends('front.layouts.app')
@inject('safeHtml','App\Services\Frontend\SafeHtml')
@section('content')
@include('front.partials.breadcrumbs')
@if(isset($page) && $page->bannerMedia)@include('front.partials.innerbanner',['banner'=>$page->bannerMedia])@endif
@if(isset($items))
    @include('front.catalogues.'.$kind)
@elseif($kind === 'contact')
    @include('front.sections.contact')
@elseif($kind === 'sitemap')
    @include('front.sections.sitemap')
@else
    @include('front.catalogues.article')
@endif
@endsection
