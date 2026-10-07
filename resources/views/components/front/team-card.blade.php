@props(['member'])

<article class="team-card">
    <a class="team-card__portrait" href="{{ route('public.team.show', $member->id) }}" aria-label="View {{ $member->name }}'s profile">
        <x-front.image :media="$member->photoMedia" :alt="$member->name" class="team-card__photo" width="400" height="460" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 25vw" fallback="front/images/dynamic/male-placeholder.jpg" />
    </a>
    <div class="team-card__content">
        @if($member->designation)
            <p class="team-card__position">{{ $member->designation }}</p>
        @endif
        <h3 class="team-card__name">
            <a href="{{ route('public.team.show', $member->id) }}">{{ $member->name }}</a>
        </h3>
        <a class="team-card__profile" href="{{ route('public.team.show', $member->id) }}" aria-label="View {{ $member->name }}'s profile">
            <span>View Profile</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 12h16m-6-6 6 6-6 6" /></svg>
        </a>
    </div>
</article>
