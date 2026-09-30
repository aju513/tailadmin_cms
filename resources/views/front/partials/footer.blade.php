<div class="footer__illustration" aria-hidden="true">
    <img src="/front/images/svg/footer.svg" alt="" />
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
                                    <div class="flex items-start gap-3 footer__contact-item mb-3 last:mb-0">
                                        <div class="footer__contact-item-image ">
                                            <span class="text-sm text-white icon-location" aria-hidden="true"></span>
                                        </div>
                                        <div class="footer__contact-item-content">
                                            <div class="text-[15px]">{{ $settings['address'] }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 footer__contact-item mb-3 last:mb-0">
                                        <div class="footer__contact-item-image ">
                                            <span class="text-sm text-white icon-envelope" aria-hidden="true"></span>
                                        </div>
                                        <div class="footer__contact-item-content">
                                            <a href="mailto:{{ $settings['email'] }}" class="text-[15px] break-all transition-all duration-500 text-text_color hover:text-secondary">
                                                {{ $settings['email'] }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 footer__contact-item mb-3 last:mb-0">
                                        <div class="footer__contact-item-image ">
                                            <span class="text-sm text-white icon-phone" aria-hidden="true"></span>
                                        </div>
                                        <div class="footer__contact-item-content">
                                            <a href="tel:{{ $settings['phone'] }}" class="text-[15px] transition-all duration-500 text-text_color hover:text-secondary">{{ $settings['phone'] }}</a>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3 footer__contact-item mb-3 last:mb-0">
                                        <div class="footer__contact-item-image">
                                            <span class="text-sm text-white icon-clock" aria-hidden="true"></span>
                                        </div>
                                        <div class="footer__contact-item-content footer__hours">
                                            <div class="mb-1 text-[15px] font-semibold">Office Hours</div>
                                            <div class="text-sm leading-5">Summer (Magh 16–Kartik 15): Sun–Fri, 9:00 AM–5:00 PM</div>
                                            <div class="mt-1 text-sm leading-5">Winter (Kartik 16–Magh 15): Sun–Fri, 9:00 AM–4:00 PM</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="footer__social">
                                    @if($settings['facebook_url'])<div class="footer__social-item">
                                        <a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="facebook" class="group w-8 h-8 bg-[#c8e7f6] flex items-center justify-center hover:bg-[#3b5998] hover:border-[#3b5998] rounded-full transition-all duration-500">
                                            <span class="transition-all duration-500 icon-facebook text-primary group-hover:text-white"></span>
                                        </a>
                                    </div>@endif
                                    @if($settings['instagram_url'])<div class="footer__social-item">
                                        <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="instagram" class="group w-8 h-8 bg-[#c8e7f6] flex items-center justify-center hover:bg-[#c32aa3] hover:border-[#c32aa3] rounded-full transition-all duration-500">
                                            <span class="transition-all duration-500 icon-instagram text-primary group-hover:text-white"></span>
                                        </a>
                                    </div>@endif
                                    @if($settings['linkedin_url'])<div class="footer__social-item">
                                        <a href="{{ $settings['linkedin_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="linkedin" class="group w-8 h-8 bg-[#c8e7f6] flex items-center justify-center hover:bg-[#0A66C2] hover:border-[#0A66C2] rounded-full transition-all duration-500">
                                            <span class="transition-all duration-500 icon-linkedin text-primary group-hover:text-white"></span>
                                        </a>
                                    </div>@endif
                                    @if($settings['x_url'])<div class="footer__social-item">
                                        <a href="{{ $settings['x_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="x" class="group w-8 h-8 bg-[#c8e7f6] flex items-center justify-center hover:bg-black hover:border-black rounded-full transition-all duration-500">
                                            <span class="transition-all duration-500 icon-x text-primary group-hover:text-white"></span>
                                        </a>
                                    </div>@endif
                                    @if($settings['youtube_url'])<div class="footer__social-item">
                                        <a href="{{ $settings['youtube_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="youtube" class="group w-8 h-8 bg-[#c8e7f6] flex items-center justify-center hover:bg-[#ff0000] hover:border-[#ff0000] rounded-full transition-all duration-500">
                                            <span class="transition-all duration-500 icon-youtube text-primary group-hover:text-white"></span>
                                        </a>
                                    </div>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-3">
                    <div class="footer__links">
                        <div class="text-primary uppercase font-heading text-xl font-bold mb-5">Important Links</div>
                        <ul class="columns-1 gap-y-5 gap-5 flex-wrap">
@foreach($importantNavigation as $link)<li><a href="{{ $link['href'] }}" target="_blank" rel="noopener noreferrer" class="text-[15px] transition-all duration-500 text-text_color hover:text-secondary">{{ $link['label'] }}</a></li>@endforeach
</ul>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                    <div class="footer__links">
                        <div class="text-primary uppercase font-heading text-xl font-bold mb-5">Quick Links</div>
                        <ul class="columns-2 gap-y-5 gap-5 flex-wrap">
@foreach($footerNavigation as $link)<li><a href="{{ $link['href'] }}" class="text-[15px] transition-all duration-500 text-text_color hover:text-secondary">{{ $link['label'] }}</a></li>@endforeach
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
                    © {{ now()->year }}, {{ $settings['site_name'] }}. {{ $settings['footer_text'] ?: 'All Rights Reserved.' }}
                </div>
      
            </div>
        </div>
    </div>
</footer>
