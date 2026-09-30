@if($paginator->hasPages())
<nav class="mt-10 pagination-wrapper" aria-label="Pagination">
    <div class="flex justify-center item-center">
        <a @if(!$paginator->onFirstPage()) href="{{ $paginator->previousPageUrl() }}" rel="prev" @else aria-disabled="true" @endif aria-label="Previous page" class="flex items-center justify-center w-10 h-10 p-2 rotate-90 border border-t-0 rounded-b-[5px] pagination-wrapper__left-arrow border-primary border-opacity-15 hover:bg-secondary hover:bg-opacity-15 duration-300 transition-all hover:text-white"><span class="text-base icon icon-dropdown"></span></a>
        @foreach($elements as $element)
            @if(is_string($element))<span class="flex items-center justify-center w-10 h-10 p-2 border border-l-0 border-primary border-opacity-15">{{ $element }}</span>@endif
            @if(is_array($element))@foreach($element as $number=>$url)
                <a href="{{ $url }}" @if($number === $paginator->currentPage()) aria-current="page" @endif class="flex items-center justify-center w-10 h-10 p-2 transition-all duration-300 border border-l-0 pagination-wrapper__counter pagination-wrapper__left-arrow border-primary border-opacity-15 hover:bg-secondary hover:bg-opacity-15 hover:text-white">{{ $number }}</a>
            @endforeach @endif
        @endforeach
        <a @if($paginator->hasMorePages()) href="{{ $paginator->nextPageUrl() }}" rel="next" @else aria-disabled="true" @endif aria-label="Next page" class="flex items-center justify-center w-10 h-10 p-2 -rotate-90 border border-t-0 rounded-b-[5px] border-primary border-opacity-15 hover:text-white pagination-wrapper__right-arrow hover:bg-secondary hover:bg-opacity-15 duration-300 transition-all"><span class="text-base icon icon-dropdown"></span></a>
    </div>
</nav>
@endif
