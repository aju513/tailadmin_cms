<div class="contact-page" role="main">
    <div class="page-title">
        <div class="container">
            <h1>
                {{ $heading }}
            </h1>
        </div>
    </div>
    <div class="contact-page__description">
        <div class="container">
            <div class="contact-page__content lg:w-4/5 text-[15px] text-text_color">@if(isset($page) && $page->summary){!! $safeHtml->clean($page->summary) !!}@else
                For information about our training, capacity development, research, and consultancy services, please contact the Provincial Centre for Good Governance using the details below.
            @endif</div>
        </div>
    </div>
    <div class="contact-page__info">
        <div class="container-fluid">
            <div class="container">
                <div class="grid grid-cols-12 gap-5 gap-y-10 mb-10 lg:mb-15">
                    <div class="col-span-12 lg:col-span-6">
                        <div class="contact-page__info-wrapper">
                            <div class="contact-page__info-title lg:max-w-4/5">
                                {{ $settings['office_name'] ?: $settings['site_name'] }}
                            </div>
                            <div class="text-text_color mb-6 lg:max-w-5/6">A Lumbini Province institution supporting good governance, institutional development, and capacity building.</div>
                            <div class="contact-page__info-item">
                                <div class="contact-page__info-item-icon">
                                    <span class="text-2xl text-secondary icon-location" aria-hidden="true"></span>
                                </div>
                                <div class="contact-page__info-item-content">
                                    <div class="text-sm text-[#718196]">Address</div>
                                    <div class="text-sm text-text_color font-medium">{{ $settings['address'] }}</div>
                                </div>
                            </div>
                            <div class="contact-page__info-item">
                                <div class="contact-page__info-item-icon">
                                    <span class="text-2xl text-secondary icon-envelope" aria-hidden="true"></span>
                                </div>
                                <div class="contact-page__info-item-content">
                                    <div class="text-sm text-[#718196]">Email</div>
                                    <a href="mailto:{{ $settings['email'] }}" class="text-sm text-text_color font-medium transition-all duration-500 hover:text-secondary break-all">{{ $settings['email'] }}</a>
                                </div>
                            </div>
                            <div class="contact-page__info-item">
                                <div class="contact-page__info-item-icon">
                                    <span class="text-2xl text-secondary icon-phone" aria-hidden="true"></span>
                                </div>
                                <div class="contact-page__info-item-content">
                                    <div class="text-sm text-[#718196]">Phone</div>
                                    <a href="tel:{{ $settings['phone'] }}" class="text-sm text-text_color font-medium transition-all duration-500 hover:text-secondary">{{ $settings['phone'] }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 lg:col-span-6">
                        <div class="contact-page__form">
                            <form class="floating-form " method="POST" action="{{ route('public.contact.send') }}">@csrf @if(session('contact_success'))<p role="status">{{ session('contact_success') }}</p>@endif @if($errors->any())<div role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                                <div class="font-poppins text-xl text-primary font-bold mb-4">Send us a message</div>
                                <div class="relative z-0 w-full mb-6 group">
                                    <input
                                        type="text"
                                        name="name" value="{{ old('name') }}" maxlength="180"
                                        id="name"
                                        class="block w-full rounded-md text-heading_color peer focus:outline-none focus:ring-0"
                                        placeholder=" "
                                        required />
                                    <label
                                        for="name"
                                        class="absolute pl-4 duration-300 origin-left transform scale-75 -translate-y-3 text-text_color peer-focus:text-text_color peer-focus:dark:text-text_color top-3 focus:pl-0 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:inset-s-0 peer-focus:-translate-y-3 peer-focus:scale-75 rtl:peer-focus:translate-x-1/4">
                                        Name<span class="text-red-700">*</span>
                                    </label>
                                </div>
                                <div class="relative z-0 w-full mb-6 group">
                                    <input
                                        type="email"
                                        name="mail" value="{{ old('mail') }}" maxlength="255"
                                        id="mail"
                                        class="block w-full rounded-md appearance-none text-heading_color peer focus:outline-none focus:ring-0"
                                        placeholder=" "
                                        required />
                                    <label
                                        for="mail"
                                        class="absolute pl-4 duration-300 origin-left transform scale-75 -translate-y-3 text-text_color peer-focus:text-text_color peer-focus:dark:text-text_color top-3 focus:pl-0 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:inset-s-0 peer-focus:-translate-y-3 peer-focus:scale-75 rtl:peer-focus:translate-x-1/4">
                                        Email Address<span class="text-red-700">*</span>
                                    </label>
                                </div>
                                <div class="grid md:grid-cols-2 md:gap-6 ">
                                    <div class="relative z-0 w-full group mb-6">
                                        <input
                                            type="text"
                                            name="phone" value="{{ old('phone') }}" maxlength="100"
                                            id="phone"
                                            class="block w-full rounded-md appearance-none text-heading_color peer focus:outline-none focus:ring-0"
                                            placeholder=" "
                                            required />
                                        <label
                                            for="phone"
                                            class="absolute pl-4 duration-300 origin-left transform scale-75 -translate-y-3 text-text_color peer-focus:text-text_color peer-focus:dark:text-text_color top-3 focus:pl-0 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:inset-s-0 peer-focus:-translate-y-3 peer-focus:scale-75 rtl:peer-focus:translate-x-1/4">
                                            Phone Number<span class="text-red-700">*</span>
                                        </label>
                                    </div>
                                    <div class="relative z-0 w-full group mb-2">
                                        <select
                                            name="country"
                                            id="country"
                                            class="block w-full rounded-md appearance-none text-heading_color peer focus:outline-none focus:ring-0">
                                            <option value="">Choose a country</option>
                                            <option value="NEP" @selected(old('country', 'NEP') === 'NEP')>
                                                Nepal
                                            </option>
                                            <option value="US" @selected(old('country') === 'US')>United States</option>
                                            <option value="CA" @selected(old('country') === 'CA')>Canada</option>
                                            <option value="FR" @selected(old('country') === 'FR')>France</option>
                                            <option value="DE" @selected(old('country') === 'DE')>Germany</option>
                                        </select>
                                        <label
                                            for="country"
                                            class="absolute pl-4 duration-300 origin-left transform scale-75 -translate-y-3 text-text_color peer-focus:text-text_color peer-focus:dark:text-text_color top-3 focus:pl-0 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:inset-s-0 peer-focus:-translate-y-3 peer-focus:scale-75 rtl:peer-focus:translate-x-1/4">
                                            Choose a Country
                                            <span class="text-red-700">*</span>
                                        </label>
                                        <small class="font-semibold text-red-500">
                                            Country field is required
                                        </small>
                                    </div>
                                </div>
                                <div class="relative z-0 w-full mb-6">
                                    <textarea
                                        name="message" maxlength="5000"
                                        id="extrainfo"
                                        rows="4"
                                        class="block w-full rounded-md appearance-none text-heading_color peer focus:outline-none focus:ring-0"
                                        placeholder=" "
                                        required>{{ old('message') }}</textarea>
                                    <label
                                        for="extrainfo"
                                        class="absolute pl-4 duration-300 origin-left transform scale-75 -translate-y-3 text-text_color peer-focus:text-text_color peer-focus:dark:text-text_color top-3 focus:pl-0 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:scale-100 peer-focus:inset-s-0 peer-focus:-translate-y-3 peer-focus:scale-75 rtl:peer-focus:translate-x-1/4">
                                        Questions / Comments*
                                    </label>
                                </div>
                                <button type="submit" class="btn-outline-secondary hav-icon px-4 py-1.5 rounded-lg group lg:mt-5 ">
                                    Send Message
                                    <span
                                        class="inline-block ml-1 text-xl transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="contact-page__map">
                    @if($mapEmbedUrl || !$settings['map_url'])<iframe src="{{ $mapEmbedUrl ?: 'https://www.google.com/maps?q=Nepalgunj%2C%20Banke%2C%20Nepal&output=embed' }}" allowfullscreen="" height="380" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map showing the Provincial Centre for Good Governance in Nepalgunj, Banke"></iframe>@else<a class="btn-outline-secondary" href="{{ $settings['map_url'] }}" target="_blank" rel="noopener noreferrer">View office location</a>@endif
                </div>
            </div>
        </div>
    </div>
</div>
