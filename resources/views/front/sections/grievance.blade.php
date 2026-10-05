<section class="grievance-page" aria-label="Grievance form">
    <div class="page-title"><div class="container"><h1>{{ $heading }}</h1></div></div>
    <div class="container pb-12 lg:pb-20">
        <div class="mx-auto max-w-4xl">
            @if($page->summary)<div class="mb-6 text-text_color">{!! $safeHtml->clean($page->summary) !!}</div>@endif
            @if($page->body)<div class="prose mb-6 max-w-none">{!! $safeHtml->clean($page->body) !!}</div>@endif
            @if(session('grievance_success'))<p role="status" class="mb-6 rounded-lg border border-primary/20 bg-primary/5 p-4 text-primary">{{ session('grievance_success') }}</p>@endif
            @if($errors->any())<div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @if(! $recaptchaConfigured)
                <p role="status" class="rounded-lg border border-primary/20 bg-primary/5 p-5 text-text_color">The grievance form is temporarily unavailable. Please try again later.</p>
            @else
                <form method="POST" action="{{ route('public.grievances.store', $page->id) }}" enctype="multipart/form-data" data-grievance-form data-site-key="{{ $settings['recaptcha_site_key'] }}" class="rounded-xl border border-primary/15 bg-white p-5 shadow-sm md:p-8">
                    @csrf
                    <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
                    <input type="hidden" name="recaptcha_token" value="">
                    <p class="mb-6 text-sm text-text_color">Share your grievance below. Contact details are optional. Fields marked <span class="text-red-700">*</span> are required.</p>
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach(['full_name' => ['Full name', 'text', 255, 'name'], 'email' => ['Email address', 'email', 255, 'email'], 'phone' => ['Phone number', 'tel', 40, 'tel'], 'subject' => ['Subject', 'text', 255, 'off']] as $name => [$label, $type, $max, $autocomplete])
                            <div>
                                <label for="grievance-{{ $name }}" class="mb-2 block text-sm font-medium text-heading_color">{{ $label }}</label>
                                <input id="grievance-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" maxlength="{{ $max }}" autocomplete="{{ $autocomplete }}" class="w-full rounded-lg border border-primary/20 px-4 py-3 text-heading_color focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" @error($name) aria-invalid="true" aria-describedby="grievance-{{ $name }}-error" @enderror>
                                @error($name)<p id="grievance-{{ $name }}-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        <div class="md:col-span-2">
                            <label for="grievance-message" class="mb-2 block text-sm font-medium text-heading_color">Grievance message <span class="text-red-700">*</span></label>
                            <textarea id="grievance-message" name="message" rows="7" required maxlength="10000" class="w-full rounded-lg border border-primary/20 px-4 py-3 text-heading_color focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" @error('message') aria-invalid="true" aria-describedby="grievance-message-error" @enderror>{{ old('message') }}</textarea>
                            @error('message')<p id="grievance-message-error" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="grievance-attachment" class="mb-2 block text-sm font-medium text-heading_color">Attachment (optional)</label>
                            <input id="grievance-attachment" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" aria-describedby="grievance-attachment-help" class="w-full rounded-lg border border-primary/20 p-3 text-sm text-text_color file:mr-4 file:rounded-md file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-primary">
                            <p id="grievance-attachment-help" class="mt-2 text-sm text-text_color">PDF, JPG, PNG or Word document. Maximum 5 MB. Attachments are visible only to authorized staff.</p>
                            @error('attachment')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p data-grievance-error role="alert" class="mt-4 text-sm text-red-700" hidden></p>
                    <noscript><p class="mt-4 text-red-700">Enable JavaScript to verify and submit this form.</p></noscript>
                    <button type="submit" class="mt-6 inline-flex rounded-lg bg-primary px-6 py-3 font-medium text-white transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60">Submit grievance</button>
                </form>
            @endif
        </div>
    </div>
</section>
