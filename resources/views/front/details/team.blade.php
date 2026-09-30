<div class="team-details-page" role="main">
    <div class="container">
        <div class="mt-6 team-details-page__full-content">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 sm:col-span-4 md:col-span-3">
                    <div class="team-details-page__description-image">
                        <div class="placeholder__img-wrapper">
                            <div class="w-full placeholder__img">
                                <x-front.image :media="$item->photoMedia" :alt="$item->name" width="600" height="600" class="rounded-[5px]" :priority="true" fallback="front/images/dynamic/male-placeholder.jpg" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-8 md:col-span-9">
                    <div class="page-title mt-0!">
                        <h1>
                            {{ $item->name }}
                        </h1>
                        <div class="text-xl font-bold font-outfit text-primary/80">
                            {{ $item->designation }}
                        </div>
                    </div>
                    <div class="team-details-page__description">
                        <div class="team-page__content lg:w-4/5 text-[15px] text-text_color">
@if($item->email)<a href="mailto:{{ $item->email }}">{{ $item->email }}</a>@endif @if($item->phone)<a href="tel:{{ $item->phone }}">{{ $item->phone }}</a>@endif
</div>
                    </div>
                    <div class="team-details-page__description-content">
                        <article class="common-module">
{!! $safeHtml->clean($item->bio) !!}
</article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
