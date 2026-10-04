<li>
    <a href="{{ $link['href'] }}" @if(!empty($link['external'])) target="_blank" rel="noopener noreferrer" @endif class="text-[15px] transition-all duration-500 text-text_color hover:text-secondary">{{ $link['label'] }}</a>
    @if(!empty($link['children']))
        <ul class="ml-4">
            @foreach($link['children'] as $childLink)
                @include('front.partials.important-menu-item', ['link' => $childLink])
            @endforeach
        </ul>
    @endif
</li>
