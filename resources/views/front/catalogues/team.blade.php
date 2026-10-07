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

            <div class="team-grid mb-7">
                @forelse($items as $member)
                    <x-front.team-card :member="$member" />
                @empty
                    <p class="col-span-full py-8 text-text_color">No team members published yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
