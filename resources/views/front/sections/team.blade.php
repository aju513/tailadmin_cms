<div class="homepage__team common-box pt-0">

    <div class="container-fluid">
        <div class="container hav-title-btn">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-5">
                <div class="section-title">Our Team</div>
                <div class="section-title-btn">
                    <a href="{{ route('public.team.index') }}" class="btn-outline-secondary hav-icon group">
                        See All Members
                        <span class="inline-block ml-1 text-base transition-transform duration-500 ease-in-out icon-arrow-up-right group-hover:translate-x-1" aria-hidden="true"></span>
                    </a>
                </div>
            </div>
            <div class="team-grid">
@foreach($team as $member)
<article class="team-card">
                    <x-front.image :media="$member->photoMedia" :alt="$member->name" class="team-card__photo" width="400" height="400" fallback="front/images/dynamic/male-placeholder.jpg" />
                    <p class="team-card__position">
{{ $member->designation }}
</p>
                    <h3 class="team-card__name">
<a href="{{ route('public.team.show',$member->id) }}" >{{ $member->name }}</a>
</h3>


                @if($member->email)<a class="team-card__detail" href="mailto:{{ $member->email }}">{{ $member->email }}</a>@endif
@if($member->phone)<a class="team-card__detail" href="tel:{{ $member->phone }}">{{ $member->phone }}</a>@endif
</article>
@endforeach
</div>
        </div>
    </div>
</div>
