<li>
    <a class="transition hover:text-brand-600" href="{{ $item->url() }}">{{ $item->label }}</a>
    @if($item->children->isNotEmpty())
        <ul class="ml-4 mt-2 space-y-2 border-l border-gray-200 pl-3">
            @foreach($item->children as $child)
                @include('public.partials.menu-item', ['item' => $child])
            @endforeach
        </ul>
    @endif
</li>
