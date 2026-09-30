<article class="resource-card h-full"><span class="resource-card__type">
{{ $item->category?->name }}
</span>
                                            <h3 class="resource-card__title">
{{ $item->title }}
</h3><a href="{{ route('public.resources.show',$item->slug) }}" class="btn-primary hav-icon group relative z-10 mt-auto"><span>View
                                                    Details</span><span
                                                    class="btn-primary__icon icon-arrow-up-right"
                                                    aria-hidden="true"></span></a>
                                        </article>
