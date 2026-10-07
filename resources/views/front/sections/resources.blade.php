<div class="homepage__resources common-box hav-title-btn pb-0">
    <div class="container">
        <div class="grid grid-cols-12 gap-x-5">
            <div class="col-span-12 lg:col-span-5">
                <div class="section-title mb-3! lg:max-w-102.25">Resources</div>
                <p>Explore reports, publications, training materials, and practical guides created to support better
                    learning and public service.</p>
                <div class="section-title-btn pl-0!">
                    <a href="{{ route('public.resources.index') }}" class="btn-outline-secondary hav-icon group mt-3">
                        View All Resources
                        <span
                            class="inline-block ml-1 text-base transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1"></span>
                    </a>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-7">
                <div class="resource-library" data-resource-tabs>
                    <div class="resource-tabs" role="tablist" aria-label="Resource categories">
@foreach($resourceGroups as $categoryId=>$group)
<button type="button" class="resource-tab {{ $loop->first ? 'is-active' : '' }}" id="resource-tab-{{ $categoryId }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-controls="resource-panel-{{ $categoryId }}" data-resource-tab="{{ $categoryId }}">{{ $group->first()->category?->name }}</button>
@endforeach
</div>
                    <div class="resource-panels">
@foreach($resourceGroups as $categoryId=>$group)
<div id="resource-panel-{{ $categoryId }}" aria-labelledby="resource-tab-{{ $categoryId }}" role="tabpanel" data-resource-panel="{{ $categoryId }}" @if(!$loop->first) hidden @endif>
                            <div class="resource-swiper swiper">
                                <div class="swiper-wrapper">
@foreach($group as $item)<div class="swiper-slide">@include('front.components.resource-card')</div>@endforeach
</div>
                            </div>
                            <div class="mt-5 flex justify-end gap-3">
                                <button type="button"
                                    class="resource-prev grid h-9.5 w-9.5 cursor-pointer place-items-center rounded-full bg-primary transition-all duration-500 swiper-button-disabled"
                                    aria-label="Previous resources"><span
                                        class="icon-prev icon flex -translate-x-px items-center justify-center text-2xl leading-none! text-white"></span></button>
                                <button type="button"
                                    class="resource-next grid h-9.5 w-9.5 cursor-pointer place-items-center rounded-full bg-primary transition-all duration-500"
                                    aria-label="Next resources"><span
                                        class="icon-back icon flex translate-x-px items-center justify-center text-2xl leading-none! text-white"></span></button>
                            </div>
                        </div>
@endforeach
</div>
                </div>
            </div>
        </div>
    </div>
</div>
