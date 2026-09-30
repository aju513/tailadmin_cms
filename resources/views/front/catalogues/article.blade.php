<div class="common-box common-page pt-0" role="main">
    <div class="container">
        <div class="page-title">
            <h1>
                {{ $heading }}
            </h1>
        </div>
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 lg:col-span-11">
                <article>
{!! $safeHtml->clean($page->summary) !!}
{!! $safeHtml->clean($page->body) !!}
</article>
            </div>
        </div>
    </div>
</div>
