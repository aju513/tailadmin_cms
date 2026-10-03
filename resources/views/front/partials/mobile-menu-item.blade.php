<li @if(!empty($navItem['children'])) class="parent" @endif>
    @if(!empty($navItem['children']))
        <button type="button" class="open-menu" aria-expanded="false" aria-label="Expand {{ $navItem['label'] }}">
            {{ $navItem['label'] }}
            <span aria-hidden="true"><span class="icon-plus text-base"></span><span class="icon-minus text-base"></span></span>
        </button>
        <ul>@foreach($navItem['children'] as $childItem)@include('front.partials.mobile-menu-item',['navItem'=>$childItem])@endforeach</ul>
    @else
        <a href="{{ $navItem['href'] }}" @if(!empty($navItem['external'])) target="_blank" rel="noopener noreferrer" @endif>{{ $navItem['label'] }}</a>
    @endif
</li>
