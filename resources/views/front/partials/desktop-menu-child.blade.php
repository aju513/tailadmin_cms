<li class="relative menu-branch">
    @if(!empty($childItem['children']))
        <button type="button" aria-label="Toggle {{ $childItem['label'] }} submenu" aria-expanded="false" class="nested-dropdown-wrap flex justify-between text-sm font-medium text-text_color transition-all duration-200 hover:text-primary">
            {{ $childItem['label'] }}<span class="icon icon-dropdown" aria-hidden="true"></span>
        </button>
        <ul class="nested-dropdown w-72 p-3">@foreach($childItem['children'] as $nestedChild)@include('front.partials.desktop-menu-child',['childItem'=>$nestedChild])@endforeach</ul>
    @else
        <a href="{{ $childItem['href'] }}" @if(!empty($childItem['external'])) target="_blank" rel="noopener noreferrer" @endif class="nested-dropdown-wrap flex justify-between text-sm font-medium text-text_color transition-all duration-200 hover:text-primary">{{ $childItem['label'] }}</a>
    @endif
</li>
