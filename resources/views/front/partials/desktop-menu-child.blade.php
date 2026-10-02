<li class="relative menu-branch">
    <a href="{{ $childItem['href'] }}" class="nested-dropdown-wrap flex justify-between text-sm font-medium text-text_color transition-all duration-200 hover:text-primary">{{ $childItem['label'] }}@if(!empty($childItem['children']))<span class="icon-dropdown"></span>@endif</a>
    @if(!empty($childItem['children']))<ul class="nested-dropdown w-72 p-3">@foreach($childItem['children'] as $nestedChild)@include('front.partials.desktop-menu-child',['childItem'=>$nestedChild])@endforeach</ul>@endif
</li>
