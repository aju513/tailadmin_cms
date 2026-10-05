@props(['media'=>null,'alt'=>'','priority'=>false,'width'=>600,'height'=>400,'fallback'=>'front/images/placeholder-logo.svg','sizes'=>'(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw'])
@inject('images','App\Services\Frontend\ImageService')
@php($image=$images->attributes($media))
@if($image)
    <img src="{{ $image['src'] }}" @if($image['srcset']) srcset="{{ $image['srcset'] }}" sizes="{{ $sizes }}" @endif alt="{{ $media->alt_text ?: $alt }}" width="{{ $width }}" height="{{ $height }}" loading="{{ $priority ? 'eager' : 'lazy' }}" @if($priority) fetchpriority="high" @endif decoding="async" {{ $attributes }}>
@else<img src="{{ asset($fallback) }}" alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}" loading="{{ $priority ? 'eager' : 'lazy' }}" decoding="async" {{ $attributes->class(['logo-placeholder' => $fallback === 'front/images/placeholder-logo.svg']) }}>@endif
