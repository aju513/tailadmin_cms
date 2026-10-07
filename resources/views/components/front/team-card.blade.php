@props(['member'])

<article class="team-card">
    <a class="team-card__portrait" href="{{ route('public.team.show', $member->id) }}" aria-label="View {{ $member->name }}'s profile">
        <x-front.image :media="$member->photoMedia" :alt="$member->name" class="team-card__photo" width="400" height="400" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw" fallback="front/images/dynamic/male-placeholder.jpg" />
    </a>
    <div class="team-card__content">
        @if($member->designation)
            <p class="team-card__position">{{ $member->designation }}</p>
        @endif
        <h3 class="team-card__name">
            <a href="{{ route('public.team.show', $member->id) }}">{{ $member->name }}</a>
        </h3>
        <a class="team-card__profile btn-venue btn-venue--compact" href="{{ route('public.team.show', $member->id) }}" aria-label="View {{ $member->name }}'s profile">
            <span class="btn-venue__text">View Profile</span>
            <span class="btn-venue__icon icon-arrow-up-right" aria-hidden="true"></span>
        </a>
    </div>
</article>
