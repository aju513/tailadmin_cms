<div class="team-page" role="main">
    <div class="container">
        <div class="page-title">
            <h1>{{ $heading }}</h1>
        </div>

        <div class="team-page__description">
            <div class="team-page__content lg:w-4/5 text-[15px] text-text_color">
{!! $safeHtml->clean($page->summary ?? '') !!}
</div>
        </div>

        <nav class="team-filters" aria-label="Team categories">
            <a class="team-filter {{ !request('team_category_id') ? 'is-active' : '' }}" href="{{ url()->current() }}" @if(!request('team_category_id')) aria-current="page" @endif>All Members</a>
            @foreach($teamCategories as $id=>$name)
                <a class="team-filter {{ (string)request('team_category_id') === (string)$id ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['team_category_id'=>$id,'page'=>null]) }}" @if((string)request('team_category_id') === (string)$id) aria-current="page" @endif>{{ $name }}</a>
            @endforeach
        </nav>

        <section class="mt-10 team-list__wrapper" aria-labelledby="team-category-title">
            <h2 id="team-category-title" class="team-list__wrapper-title">
                {{ $teamCategories->get(request('team_category_id'), 'Our Team') }}
            </h2>

            <div class="grid grid-cols-12 gap-5 mb-7">@forelse($items as $member)
<div class="col-span-12 sm:col-span-6 lg:col-span-3">
                            <div class="team-list__item">
                                <div class="team-list__item-image">
                                    <div class="placeholder__img-wrapper">
                                        <div class="w-full placeholder__img">
                                            <a href="{{ route('public.team.show',$member->id) }}">
                                                <x-front.image :media="$member->photoMedia" :alt="$member->name" width="600" height="600" class="rounded-[5px]" fallback="front/images/dynamic/male-placeholder.jpg" />
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="team-list__item-content">
                                    <div class="mt-2 -mb-2 text-base font-bold text-text_color">
                                        {{ $member->name }}
                                    </div>
                                    <span class="text-xs leading-3 text-[#797ea6]">
                                        {{ $member->designation }}
                                    </span>
                                    <?php if ($member->email || $member->phone): ?>
                                        <div class="mt-3 space-y-1 text-xs text-text_color">
                                            <?php if ($member->email): ?>
                                                <a class="block break-all hover:text-primary" href="mailto:{{ $member->email }}">
                                                    {{ $member->email }}
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($member->phone): ?>
                                                <a class="block hover:text-primary" href="tel:{{ $member->phone }}">
                                                    {{ $member->phone }}
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
@empty<p class="col-span-12 py-8 text-text_color">No team members published yet.</p>@endforelse</div>
        </section>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
