<section class="mt-8" aria-label="Resources">
    <h2 class="mb-5 text-2xl font-semibold">Resources and downloads</h2>
    <div class="grid gap-4 sm:grid-cols-2">
        @forelse($resources as $resource)
            <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="mb-2 text-xs font-medium text-gray-500">{{ $resource->category->name }} @if($resource->published_at) ? {{ $resource->published_at->format('d M Y') }} @endif</p>
                <h3 class="text-lg font-semibold"><a href="{{ route('public.resources.show', $resource->slug) }}" class="hover:text-brand-600">{{ $resource->title }}</a></h3>
                @if($resource->description)<p class="mt-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($resource->description, 180) }}</p>@endif
                <div class="mt-5 flex gap-4 text-sm font-medium text-brand-600">
                    <a href="{{ route('public.resources.show', $resource->slug) }}" class="hover:underline">View details</a>
                    <a href="{{ route('public.resources.download', $resource->slug) }}" class="hover:underline">Download</a>
                </div>
            </article>
        @empty
            <p class="rounded-xl border border-gray-200 bg-white p-6 text-sm text-gray-500 sm:col-span-2">No published resources are available yet.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $resources->links() }}</div>
</section>
