<section class="contact-page" aria-label="Contact us">
    <div class="page-title"><div class="container"><h1>{{ $heading }}</h1></div></div>
    <div class="container pb-12 lg:pb-20">
        @if(isset($page) && $page->summary)<div class="mb-6 text-text_color">{!! $safeHtml->clean($page->summary) !!}</div>@endif
        @if(isset($page) && $page->body)<div class="prose mb-6 max-w-none">{!! $safeHtml->clean($page->body) !!}</div>@endif
        <div class="grid gap-8 {{ $settings['map_url'] ? 'lg:grid-cols-2' : '' }}">
            <div class="rounded-xl border border-primary/15 bg-primary/5 p-6 md:p-8">
                <h2 class="mb-6 text-xl font-bold text-primary">{{ $settings['site_name'] }}</h2>
                <dl class="space-y-5 text-text_color">
                    @foreach(['address' => 'Address', 'email' => 'Site email', 'phone' => 'Phone', 'website_url' => 'Website', 'office_hours' => 'Office hours'] as $key => $label)
                        @if(filled($settings[$key]))
                            <div><dt class="mb-1 text-sm font-medium text-primary">{{ $label }}</dt><dd class="whitespace-pre-line break-words">@if($key === 'email')<a href="mailto:{{ $settings[$key] }}" class="hover:underline">{{ $settings[$key] }}</a>@elseif($key === 'phone')<a href="tel:{{ $settings[$key] }}" class="hover:underline">{{ $settings[$key] }}</a>@elseif($key === 'website_url')<a href="{{ $settings[$key] }}" target="_blank" rel="noopener noreferrer" class="hover:underline">{{ $settings[$key] }}</a>@else{{ $settings[$key] }}@endif</dd></div>
                        @endif
                    @endforeach
                </dl>
                @if($settings['contact_officer_name'] || $settings['contact_officer_phone'])
                    <div class="mt-6 border-t border-primary/15 pt-6"><h3 class="mb-2 font-bold text-primary">Contact person</h3>@if($settings['contact_officer_name'])<p class="text-text_color">{{ $settings['contact_officer_name'] }}</p>@endif @if($settings['contact_officer_phone'])<a href="tel:{{ $settings['contact_officer_phone'] }}" class="text-primary hover:underline">{{ $settings['contact_officer_phone'] }}</a>@endif</div>
                @endif
                <div class="mt-6">@include('front.components.social-links')</div>
            </div>
            @if($settings['map_url'])
                <div class="overflow-hidden rounded-xl border border-primary/15">
                    @if($mapEmbedUrl)<iframe src="{{ $mapEmbedUrl }}" width="100%" height="450" class="h-full min-h-[350px] w-full" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Office location: {{ $settings['site_name'] }}"></iframe>@else<div class="flex min-h-[350px] items-center justify-center bg-primary/5 p-6"><a href="{{ $settings['map_url'] }}" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-primary px-5 py-3 text-white">View office location</a></div>@endif
                </div>
            @endif
        </div>
    </div>
</section>
