@php($officialUrl = '')
<div class="resource-detail-page" role="main">
    <div class="page-title">
        <div class="container">
            <p class="resource-detail-page__category"><?= htmlspecialchars($item->category?->name ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <h1><?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="resource-detail-page__intro">{{ strip_tags($item->description ?? '') }}</p>
            <p class="mt-4 text-sm text-text_color">
                <span class="font-semibold">Published:</span> <?= htmlspecialchars($item->published_at?->format('d M, Y') ?? '', ENT_QUOTES, 'UTF-8') ?>
                <span class="mx-2 text-primary/40" aria-hidden="true">|</span>
                <span class="font-semibold">Publisher:</span> <?= htmlspecialchars($settings['office_name'] ?: $settings['site_name'], ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </div>

    <div class="common-box pt-0">
        <div class="container">
            <div class="resource-detail-page__toolbar">
                <h2>Official document</h2>
                <?php if ($item->fileMedia !== null): ?>
                    <div class="resource-detail-page__actions">
                        <a href="<?= htmlspecialchars($item->fileMedia->url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Open in new tab</a>
                        <a href="<?= htmlspecialchars($item->fileMedia->url(), ENT_QUOTES, 'UTF-8') ?>" download="{{ $item->fileMedia->original_name }}">Download file</a>
                    </div>
                <?php elseif ($officialUrl !== ''): ?>
                    <div class="resource-detail-page__actions">
                        <a href="<?= htmlspecialchars($officialUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">View on official website</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($item->fileMedia === null): ?>
                <div class="resource-detail-page__empty">
                    <span class="icon-download" aria-hidden="true"></span>
                    <?php if ($officialUrl !== ''): ?>
                        <h3>Original document available from PCGG</h3>
                        <p>Open the official Acts and Rules listing and select this title to view or download its published file.</p>
                        <a class="btn-secondary hav-icon mt-4" href="<?= htmlspecialchars($officialUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">
                            <span>Open official listing</span>
                            <span class="btn-secondary__icon icon-arrow-up-right" aria-hidden="true"></span>
                        </a>
                    <?php else: ?>
                        <h3>File not available yet</h3>
                        <p>The document for this resource has not been uploaded. Please check back later.</p>
                    <?php endif; ?>
                </div>
            <?php elseif (str_starts_with($item->fileMedia->mime_type, 'image/')): ?>
                <div class="resource-detail-page__viewer resource-detail-page__viewer--image">
                    <img src="<?= htmlspecialchars($item->fileMedia->url(), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8') ?>" />
                </div>
            <?php elseif ($item->fileMedia->mime_type === 'application/pdf'): ?>
                <iframe class="resource-detail-page__viewer" src="<?= htmlspecialchars($item->fileMedia->url(), ENT_QUOTES, 'UTF-8') ?>" title="<?= htmlspecialchars($item->title . ' file preview', ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                    <p>Preview unavailable. <a href="<?= htmlspecialchars($item->fileMedia->url(), ENT_QUOTES, 'UTF-8') ?>">Open the file</a>.</p>
                </iframe>
            <?php else: ?>
                <div class="resource-detail-page__empty">
                    <span class="icon-download" aria-hidden="true"></span>
                    <h3>Download this file</h3>
                    <p>This file format cannot be previewed in the browser. Use the download link above to open it.</p>
                </div>
            <?php endif; ?>

            <a class="resource-detail-page__back" href="{{ route('public.'.$kind.'.index') }}"><span class="icon-prev" aria-hidden="true"></span> Back to {{ $kind }}</a>
        </div>
    </div>
</div>
