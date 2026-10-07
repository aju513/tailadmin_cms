export function initHomepagePopup(doc = document, win = window) {
    const dialog = doc.getElementById('homepage-popup');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    const close = dialog.querySelector('[data-popup-close]');
    let previousFocus;
    let previousOverflow;
    const open = () => {
        if (dialog.open) return;
        previousFocus = doc.activeElement;
        previousOverflow = doc.body.style.overflow;
        dialog.showModal();
        doc.body.style.overflow = 'hidden';
        close?.focus();
    };
    close?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
        doc.body.style.overflow = previousOverflow;
        previousFocus?.focus();
    });
    // Native dialog traps focus and handles Escape; no dismissal is persisted.
    win.addEventListener('pagehide', () => { if (dialog.open) dialog.close(); });
    win.addEventListener('pageshow', event => { if (event.persisted) open(); });
    open();
}
