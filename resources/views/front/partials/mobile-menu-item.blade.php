<li @if(!empty($navItem['children'])) class="parent" @endif>
    <a href="{{ $navItem['href'] }}" @if(!empty($navItem['external'])) target="_blank" rel="noopener noreferrer" @endif>{{ $navItem['label'] }}</a>
    @if(!empty($navItem['children']))
        <span class="open-menu" role="button" tabindex="0" aria-expanded="false" aria-label="Expand {{ $navItem['label'] }}"><span class="icon-plus text-base"></span><span class="icon-minus text-base"></span></span>
        <ul>@foreach($navItem['children'] as $childItem)@include('front.partials.mobile-menu-item',['navItem'=>$childItem])@endforeach</ul>
    @endif
</li>
