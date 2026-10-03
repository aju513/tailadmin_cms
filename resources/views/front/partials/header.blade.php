@php($siteNavigation = $navigation)
<header class="relative header">

    

    <!-- =========================================
         TOP HEADER
    ========================================== -->
    <div class="header__top hidden lg:block">
        <div class="container-fluid">
            <div class="flex items-center justify-between gap-5">

                <!-- Logo -->
                <div class="biz__logo">
                    <a href="{{ route('public.home') }}" class="site-brand" aria-label="{{ $settings['site_name'] }}">
                        <img
                            class="site-brand__emblem"
                            width="150"
                            height="126"
                            src="{{ $settings['logo_url'] ?: asset(config('frontend.branding.logo')) }}"
                            alt="{{ $settings['site_name'] }}" />
                        <span class="site-brand__copy">
                             <span class="site-brand__name">{{ $settings['site_name'] }}</span>
                            <span class="site-brand__location">{{ $settings['address'] }}</span>
                        </span>
                    </a>
                </div>

                <div class="flex items-center gap-3">

                    <!-- Search -->
                    <div class="websearch-wrap">
                        <span
                            class="search-btn hidden h-10 w-10 cursor-pointer items-center justify-center rounded-full p-3 transition-all duration-500 lg:flex hover:bg-primary/90">
                            <span
                                class="icon-search text-2xl text-primary transition-all duration-500 hover:text-white">
                            </span>
                        </span>

                        <div class="search-box-wrapper relative mr-5">
                            <div class="search-box-elements hidden">

                                <a
                                    href="#"
                                    class="search-close package flex h-8 w-8 items-center justify-center">
                                    <span class="icon-close text-xs text-white"></span>
                                </a>

                                <form method="GET" action="{{ route('public.search') }}">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            id="default-search" name="q" maxlength="100"
                                            class="block h-13.75 w-full rounded-lg border border-gray-300 bg-white px-4 py-2"
                                            placeholder="Search"
                                            required />

                                        <button
                                            type="submit"
                                            class="absolute bottom-3.5 inset-e-2.5 rounded-lg bg-white text-sm font-medium">
                                            <span class="icon-search text-xl text-secondary"></span>
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                    <!-- Contact Officer -->
                    <div
                        class="header__menu-contact mr-2 hidden items-center justify-end gap-2 overflow-hidden lg:flex">

                        <div
                            class="header__menu-contact-icon h-11.25 w-11.25 overflow-hidden rounded-full">
                            <img
                                class="h-full w-full object-cover"
                                src="{{ $settings['contact_officer_photo_url'] ?: asset('front/images/dynamic/male-placeholder.jpg') }}"
                                alt="Contact Officer">
                        </div>

                        <div class="w-32 text-left">
                            <span
                                class="block text-[11px] leading-3 text-text_color">
                                Contact Officer
                            </span>

                            <a
                                class="mt-1 flex items-center justify-start gap-2 text-sm font-semibold leading-3 text-primary transition-all duration-500 hover:text-secondary"
                                href="{{ route('public.contact') }}">
                                {{ $settings['contact_officer_phone'] ?: $settings['phone'] ?: 'Contact us' }}
                            </a>
                        </div>

                    </div>

                    <!-- TMIS -->
                    <a
                        href="{{ $settings['tmis_url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="py-2 text-[16px] font-medium text-white transition-all duration-500 ease-in-out bg-secondary rounded-4xl px-3 hover:bg-primary">

                        TMIS

                        <span
                            class="icon-arrow-up-right inline-block text-base transition-transform duration-500 ease-in-out group-hover:translate-x-1 hover:rotate-0">
                        </span>
                    </a>

                    <div class="language-switcher" data-language-switcher>
                        <button type="button" class="language-switcher__toggle" aria-label="Choose language" aria-expanded="false" aria-haspopup="true">
                            <img src="/front/images/svg/flags/united-kingdom.svg" alt="" width="38" height="27" data-current-language-flag />
                        </button>
                        <div class="language-switcher__menu" hidden>
                            <button type="button" class="language-switcher__option is-selected" data-language-option data-language="en" data-flag="/front/images/svg/flags/united-kingdom.svg" aria-label="English" aria-pressed="true">
                                <img src="/front/images/svg/flags/united-kingdom.svg" alt="" width="38" height="27" />
                            </button>
                            <button type="button" class="language-switcher__option" data-language-option data-language="ne" data-flag="/front/images/svg/flags/nepal.svg" aria-label="नेपाली" aria-pressed="false">
                                <img src="/front/images/svg/flags/nepal.svg" alt="" width="38" height="27" />
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- =========================================
         DESKTOP NAVIGATION
    ========================================== -->
    <div class="header__menu hidden lg:block">
        <div class="container relative">

            <div class="flex items-center justify-center gap-2">

                <!-- Sticky Logo -->
                <div class="biz__logo">
                    <a href="{{ route('public.home') }}" class="site-brand" aria-label="{{ $settings['site_name'] }}">
                        <img
                            class="site-brand__emblem"
                            width="150"
                            height="126"
                            src="{{ $settings['logo_url'] ?: asset(config('frontend.branding.logo')) }}"
                            alt="{{ $settings['site_name'] }}" />
                        <span class="site-brand__copy">
                            <span class="site-brand__province">{{ $settings['province_name'] }}</span>
                            <span class="site-brand__name">{{ $settings['site_name'] }}</span>
                            <span class="site-brand__location">{{ $settings['address'] }}</span>
                        </span>
                    </a>
                </div>


                <nav class="nav-menu flex items-center justify-center">
    <ul class="gap-2 lg:flex lg:items-center lg:justify-between xl:gap-5">
        @foreach ($siteNavigation as $navItem)
            <li class="relative">
                @if (!empty($navItem['children']))
                    <button type="button" aria-label="Toggle {{ $navItem['label'] }} submenu" aria-expanded="false" class="dropdown-toggle inline-flex items-center gap-1 text-sm font-semibold uppercase leading-3.5 font-heading text-text_color transition-all duration-500 hover:text-secondary">
                        {{ $navItem['label'] }}
                        <span class="icon icon-dropdown text-sm xl:text-base" aria-hidden="true"></span>
                    </button>
                    <div class="item dropdown custom-shadow absolute left-auto top-9 z-10 hidden rounded-bl-[5px] rounded-br-[5px]">
                        <div class="flex flex-wrap justify-start">
                            <ul class="w-72 p-3">
                                @foreach($navItem['children'] as $childItem)@include('front.partials.desktop-menu-child')@endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    <a href="{{ $navItem['href'] }}" @if(!empty($navItem['external'])) target="_blank" rel="noopener noreferrer" @endif class="text-sm font-semibold uppercase leading-3.5 font-heading text-text_color transition-all duration-500 hover:text-secondary">
                        {{ $navItem['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</nav>


                <!-- Sticky Search -->
                <div class="websearch-wrap">

                    <span
                        class="search-btn hidden h-10 w-10 cursor-pointer items-center justify-center rounded-full p-3 transition-all duration-500 lg:flex hover:bg-secondary">

                        <span
                            class="icon-search text-2xl text-secondary transition-all duration-500 hover:text-white">
                        </span>

                    </span>

                    <div class="search-box-wrapper relative mr-5">

                        <div class="search-box-elements hidden">

                            <a
                                href="#"
                                class="search-close package flex h-8 w-8 items-center justify-center">
                                <span class="icon-close text-xs text-white"></span>
                            </a>

                            <form method="GET" action="{{ route('public.search') }}">
                                <div class="relative">

                                    <input
                                        type="text"
                                        id="sticky-search" name="q" maxlength="100"
                                        class="block h-13.75 w-full rounded-lg border border-gray-300 bg-white! px-4 py-2"
                                        placeholder="Search"
                                        required />

                                    <button
                                        type="submit"
                                        class="absolute bottom-3.5 inset-e-2.5 rounded-lg bg-white text-sm font-medium">

                                        <span class="icon-search text-xl text-secondary"></span>

                                    </button>

                                </div>
                            </form>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>


    <!-- =========================================
         MOBILE NAVIGATION
    ========================================== -->
    <nav class="custom-shadow mob-nav block bg-white px-3.75 py-2 lg:hidden">

        <div id="mobile-nav">

            <div class="mobile-nav__wrap flex items-center justify-between">

                <div class="biz__logo">
                    <a href="{{ route('public.home') }}" class="site-brand" aria-label="{{ $settings['site_name'] }}">
                        <img
                            class="site-brand__emblem"
                            width="72"
                            height="60"
                            src="{{ $settings['logo_url'] ?: asset(config('frontend.branding.logo')) }}"
                            alt="{{ $settings['site_name'] }}" />
                        <span class="site-brand__copy">
                            <span class="site-brand__province">{{ $settings['province_name'] }}</span>
                            <span class="site-brand__name">{{ $settings['site_name'] }}</span>
                            <span class="site-brand__location">{{ $settings['address'] }}</span>
                        </span>
                    </a>
                </div>

                <div class="flex h-7.5 items-center">

                    <div class="language-switcher language-switcher--mobile" data-language-switcher>
                        <button type="button" class="language-switcher__toggle" aria-label="Choose language" aria-expanded="false" aria-haspopup="true">
                            <img src="/front/images/svg/flags/united-kingdom.svg" alt="" width="32" height="23" data-current-language-flag />
                        </button>
                        <div class="language-switcher__menu" hidden>
                            <button type="button" class="language-switcher__option is-selected" data-language-option data-language="en" data-flag="/front/images/svg/flags/united-kingdom.svg" aria-label="English" aria-pressed="true">
                                <img src="/front/images/svg/flags/united-kingdom.svg" alt="" width="38" height="27" />
                            </button>
                            <button type="button" class="language-switcher__option" data-language-option data-language="ne" data-flag="/front/images/svg/flags/nepal.svg" aria-label="नेपाली" aria-pressed="false">
                                <img src="/front/images/svg/flags/nepal.svg" alt="" width="38" height="27" />
                            </button>
                        </div>
                    </div>

                    <span
                        id="open-search" role="button" tabindex="0" data-search-url="{{ route('public.search') }}"
                        class="flex w-full items-center gap-3 px-4">

                        <span class="icon-search text-xl text-secondary"></span>

                    </span>

                    <div
                        class="menu-button flex items-center gap-2.25 text-secondary"
                        id="menu-toggle" role="button" tabindex="0" aria-expanded="false"
                        aria-controls="nav"
                        aria-label="Toggle navigation">

                        <div class="menu-toggle">
                            <span class="menu-toggle-bar menu-toggle-bar--top"></span>
                            <span class="menu-toggle-bar menu-toggle-bar--middle"></span>
                            <span class="menu-toggle-bar menu-toggle-bar--bottom"></span>
                        </div>

                        Menu

                    </div>

                </div>

            </div>


            <div class="overflow">

                <ul id="nav">@foreach($navigation as $navItem)@include('front.partials.mobile-menu-item')@endforeach</ul>

            </div>

        </div>

    </nav>

</header>
<div class="header-height" aria-hidden="true"></div>
