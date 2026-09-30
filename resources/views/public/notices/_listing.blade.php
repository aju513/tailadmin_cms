<div class="space-y-4">
    @forelse($items as $item)
        <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="mb-2 text-xs font-medium text-gray-500">{{ $item->notice_type->label() }}</p>
            <h2 class="text-lg font-semibold"><a href="{{ route('public.notices.show', $item->slug) }}" class="hover:text-brand-600">{{ $item->title }}</a></h2>
            @if($item->description)<p class="mt-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 220) }}</p>@endif
            <time class="mt-3 block text-xs text-gray-500">{{ $item->published_at?->format('d M Y') }}</time>
            @if($item->deadline_at)
                <p class="mt-3 text-sm text-gray-600">Deadline: {{ $item->deadline_at->format('d M Y H:i') }} @if($item->deadline_at->isPast())<span class="ml-2 text-gray-500">(Deadline passed)</span>@endif</p>
            @endif
            @if($item->fileMedia)<a href="{{ $item->fileMedia->url() }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-block text-sm font-medium text-brand-600 hover:underline">View attachment</a>@endif
        </article>
    @empty
        <p class="rounded-xl bg-white p-6 text-gray-500">No published notices are available.</p>
    @endforelse
</div>
<div class="mt-8">{{ $items->links() }}</div>
