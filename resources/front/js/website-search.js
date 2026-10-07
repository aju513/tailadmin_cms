export function initWebsiteSearch(doc = document) {
    const dialog = doc.getElementById('site-search-dialog');
    if (!dialog) return;
    const input = dialog.querySelector('input[type="search"]');
    let opener;
    let previousOverflow;

    doc.querySelectorAll('[data-site-search-open]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            if (dialog.open) return;
            opener = trigger;
            previousOverflow = doc.body.style.overflow;
            dialog.showModal();
            doc.body.style.overflow = 'hidden';
            input?.focus();
        });
    });
    dialog.querySelector('.site-search-close')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        if (event.target === dialog) dialog.close();
    });
    // Native dialog handles Escape and contains keyboard focus while modal.
    dialog.addEventListener('close', () => {
        doc.body.style.overflow = previousOverflow;
        opener?.focus();
    });
}
