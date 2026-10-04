@extends('front.layouts.app')
@inject('safeHtml','App\Services\Frontend\SafeHtml')
@inject('embeds','App\Services\Frontend\VideoEmbedService')
@section('content')
@include('front.partials.breadcrumbs')
@if($kind === 'news' && ($item->bannerMedia || $item->thumbnailMedia))@include('front.partials.innerbanner',['banner'=>$item->bannerMedia ?? $item->thumbnailMedia])@endif
@include('front.details.'.(in_array($kind,['notices','resources']) ? 'document' : $kind))
@endsection
