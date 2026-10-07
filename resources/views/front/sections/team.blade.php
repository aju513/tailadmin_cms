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
                    <x-front.team-card :member="$member" />
                @endforeach
</div>
        </div>
    </div>
</div>
