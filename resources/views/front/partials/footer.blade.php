<div class="footer__illustration" aria-hidden="true">
    <img src="{{ asset(config('frontend.branding.footer_illustration')) }}" alt="" />
</div>
<footer class="footer">
 
    <div class="footer__main">
        <div class="container">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 lg:col-span-3">
                    <div class="footer__links-contact">
                        <div class="footer__contact-wrapper">
                            <div class="flex flex-col items-start justify-start gap-5">
                                <div class="footer__brand font-heading text-xl font-bold leading-tight text-white">
                                    {{ $settings['site_name'] }}
                                </div>
                                <div class="footer__contact">
                                    @foreach(['address' => 'icon-location', 'email' => 'icon-envelope', 'phone' => 'icon-phone', 'website_url' => 'icon-arrow-up-right', 'office_hours' => 'icon-clock'] as $key => $icon)
                                        @if(filled($settings[$key]))
                                            <div class="mb-3 flex items-start gap-3 footer__contact-item last:mb-0">
                                                <span class="text-sm text-white {{ $icon }}" aria-hidden="true"></span>
                                                <div class="footer__contact-item-content min-w-0 break-words text-[15px] text-white">
                                                    @if($key === 'email')
                                                        <a href="mailto:{{ $settings[$key] }}">{{ $settings[$key] }}</a>
                                                    @elseif($key === 'phone')
                                                        <a href="tel:{{ $settings[$key] }}">{{ $settings[$key] }}</a>
                                                    @elseif($key === 'website_url')
                                                        <a href="{{ $settings[$key] }}" target="_blank" rel="noopener noreferrer">{{ $settings[$key] }}</a>
                                                    @elseif($key === 'office_hours')
                                                        <div class="mb-1 font-semibold">Office Hours</div>
                                                        <div class="whitespace-pre-line text-sm leading-5">{{ $settings[$key] }}</div>
                                                    @else
                                                        {{ $settings[$key] }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @include('front.components.social-links')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-3">
                    <div class="footer__links">
                        <div class="text-primary uppercase font-heading text-xl font-bold mb-5">Important Links</div>
                        <ul class="columns-1 gap-y-5 gap-5 flex-wrap">
@foreach($importantNavigation as $link)@include('front.partials.important-menu-item', ['link' => $link])@endforeach
</ul>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <div class="footer__links">
                        <div class="text-primary uppercase font-heading text-xl font-bold mb-5">Quick Links</div>
                        <ul class="columns-2 gap-y-5 gap-5 flex-wrap">
@foreach($footerNavigation as $link)<li><a href="{{ $link['href'] }}" @if(!empty($link['external'])) target="_blank" rel="noopener noreferrer" @endif class="text-[15px] transition-all duration-500 text-text_color hover:text-secondary">{{ $link['label'] }}</a></li>@endforeach
</ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="relative flex flex-col items-center justify-center gap-y-5 z-20 pb-6">
  
            <div class="footer__copyright">
                <div class=" text-sm text-[#424242] font-semibold text-center">
                    © {{ now()->year }}, {{ $settings['site_name'] }}. {{ $settings['footer_text'] }}
                </div>
      
            </div>
        </div>
    </div>
</footer>
