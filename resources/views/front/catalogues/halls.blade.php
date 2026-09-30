@php
    $hallSpaces = $items->map(fn($record) => ['name'=>$record->title,'image'=>$record->thumbnailMedia?->url() ?: asset('front/images/placeholder-logo.svg'),'booking'=>route('public.halls.show',$record->slug),'alt'=>$record->thumbnailMedia?->alt_text ?: $record->title,'capacity'=>$record->capacity,'price'=>$record->rental_rate !== null ? 'NPR '.number_format($record->rental_rate).' / '.$record->rate_unit : 'Contact the office','availability'=>ucfirst(str_replace('_',' ',$record->availability_status))])->all();
@endphp
<div class="package-list-page hall-list-page" role="main">
    <div class="page-title">
        <div class="container">
            <h1>
                {{ $heading }}
            </h1>
        </div>
    </div>
    <div class="package-list-page__description">
        <div class="container">
            <div class="package-list-page__content lg:w-4/5 text-[15px] text-text_color">
{!! $safeHtml->clean($page->summary ?? '') !!}
</div>
        </div>
    </div>
    <div class="mt-7 package-list__wrapper package-list hav-gradient-bg common-box pb-0">
        <div class="container">
            <div class="mb-3 text-lg font-bold">
                Showing <span class="text-secondary">{{ $items->total() }}</span> halls
            </div>
            <div class="grid grid-cols-12 gap-5">
                <?php foreach ($hallSpaces as $hall): ?>
                    <?php
                    $hallName = htmlspecialchars($hall['name'], ENT_QUOTES, 'UTF-8');
                    $hallImage = htmlspecialchars($hall['image'], ENT_QUOTES, 'UTF-8');
                    $hallBooking = htmlspecialchars($hall['booking'], ENT_QUOTES, 'UTF-8');
                    $hallAlt = htmlspecialchars($hall['alt'], ENT_QUOTES, 'UTF-8');
                    $hallCapacity = htmlspecialchars($hall['capacity'], ENT_QUOTES, 'UTF-8');
                    $hallPrice = htmlspecialchars($hall['price'], ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="col-span-12 sm:col-span-6 lg:col-span-4">
                        <div class="package-list__item">
                            <div class="top-badge">{{ $hall['availability'] }}</div>
                            <div class="package-list__item-image">
                                <div class="placeholder__img-wrapper">
                                    <div class="placeholder__img">
                                        <a href="<?= $hallBooking ?>">
                                            <img
                                                src="<?= $hallImage ?>"
                                                width="600"
                                                height="450"
                                                alt="<?= $hallAlt ?>" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="package-list__item-content">
                                <h3 class="package-list__item-title">
                                    <a href="<?= $hallBooking ?>">
                                        <?= $hallName ?>
                                    </a>
                                </h3>
                                <div class="package-list__item-meta">
                                    <div class="package-list__item-calendar">
                                        <span class="icon-users text-lg text-secondary" aria-hidden="true"></span>
                                        <span class="text-xs text-text_color">Capacity: <span class="text-text_color"><?= $hallCapacity ?> seats</span></span>
                                    </div>
                                    <div class="package-list__item-passenger">
                                        <div class="package-list__item-reviews flex items-center gap-2 text-text_color text-xs">
                                            <span class="icon-tag text-lg text-secondary" aria-hidden="true"></span>
                                            <span>Price: <?= $hallPrice ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="package-list__item-bottom">
                                    <div class="package-list__item-price">
                                        <div class="text-sm text-text_color">Rental Rate</div>
                                        <div class="text-lg font-semibold text-secondary"><?= $hallPrice ?></div>
                                    </div>
                                    <div class="package-list__item-link">
                                        <a href="<?= $hallBooking ?>">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="package-list__extra-content common-box pb-0">
        <div class="container">
            <div class="grid grid-cols-12 gap-5">
                <div class="col-span-12 lg:col-span-11">
                    <article>{!! $safeHtml->clean($page->body ?? '') !!}</article>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
