@php
    $resources = $items->getCollection()->mapWithKeys(fn($record) => [$record->slug => ['title'=>$record->title, 'category'=>(string)$record->resource_category_id, 'type'=>$record->category?->name, 'description'=>Str::limit(strip_tags($record->description),200), 'published_date'=>$record->published_at?->format('d M, Y'), 'publisher'=>$settings['office_name'] ?: $settings['site_name']]])->all();
    $resourceCategories = ['all'=>'All Resources'] + $items->getCollection()->mapWithKeys(fn($record) => [(string)$record->resource_category_id=>$record->category?->name])->all();
@endphp
<div class="package-list-page resource-list-page" role="main">
    <div class="page-title">
        <div class="container"><h1>{{ $heading }}</h1></div>
    </div>
    <div class="package-list-page__description">
        <div class="container">
            <p class="package-list-page__content lg:w-4/5 text-[15px] text-text_color">
{!! $safeHtml->clean($page->summary ?? '') !!}
</p>
            
        </div>
    </div>

    <div class="mt-7 package-list__wrapper hav-gradient-bg common-box pb-0">
        <div class="container">
            <div class="resource-library" data-resource-tabs>
                <div class="resource-tabs" role="tablist" aria-label="Resource categories">
                    <?php foreach ($resourceCategories as $category => $label): ?>
                        <button type="button" id="resources-tab-<?= $category ?>" class="resource-tab<?= $category === 'all' ? ' is-active' : '' ?>" role="tab" aria-selected="<?= $category === 'all' ? 'true' : 'false' ?>" aria-controls="resources-panel-<?= $category ?>" data-resource-tab="<?= $category ?>">
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="resource-panels">
                    <?php foreach ($resourceCategories as $category => $label): ?>
                        <?php $visibleResources = array_filter($resources, static fn($item) => $category === 'all' || $item['category'] === (string) $category); ?>
                        <section id="resources-panel-<?= $category ?>" role="tabpanel" aria-labelledby="resources-tab-<?= $category ?>" data-resource-panel="<?= $category ?>"<?= $category === 'all' ? '' : ' hidden' ?>>
                            <p class="mb-5 text-lg font-bold text-text_color">Showing <span class="text-secondary"><?= count($visibleResources) ?></span> <?= count($visibleResources) === 1 ? 'resource' : 'resources' ?></p>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <?php foreach ($visibleResources as $slug => $resource): ?>
                                    <article class="training-list__item h-full">
                                        <h2 class="training-list__title"><a href="{{ route('public.resources.show', $slug) }}"><?= htmlspecialchars($resource['title'], ENT_QUOTES, 'UTF-8') ?></a></h2>
                                        <p class="resource-list-page__card-description"><?= htmlspecialchars($resource['description'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <p class="mb-4 text-xs leading-5 text-white/80">
                                            Published: <?= htmlspecialchars($resource['published_date'], ENT_QUOTES, 'UTF-8') ?><br />
                                            Publisher: <?= htmlspecialchars($resource['publisher'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                        <a href="{{ route('public.resources.show',$slug) }}" class="btn-venue btn-venue--compact mt-auto!">
                                            <span class="btn-venue__text">View Details</span>
                                            <span class="btn-venue__icon icon-arrow-up-right" aria-hidden="true"></span>
                                        </a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">{{ $items->links('front.components.pagination') }}</div>
@if(isset($page) && $page->body)<div class="common-box"><div class="container"><article>{!! $safeHtml->clean($page->body) !!}</article></div></div>@endif
