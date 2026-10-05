import { Fancybox } from '../vendor/fancybox.js';

export function initMediaViewer(doc = document, fancybox = Fancybox) {
    if (!fancybox?.bind) return;
    fancybox.bind('[data-fancybox]', {});

    const thumbnails = [...doc.querySelectorAll('[data-home-gallery]')];
    const main = doc.getElementById('homepage-about-main-image');
    const opener = doc.querySelector('[data-home-gallery-open]');
    let active = 0;
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', () => {
            active = index;
            if (main) {
                main.src = thumbnail.href;
                main.removeAttribute('srcset');
                main.alt = thumbnail.dataset.galleryAlt || '';
            }
            thumbnails.forEach((item) => item.classList.toggle('is-active', item === thumbnail));
            if (opener) opener.href = thumbnail.href;
        });
    });
    opener?.addEventListener('click', (event) => {
        if (!thumbnails.length) return;
        event.preventDefault();
        thumbnails[active].click();
    });
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => initMediaViewer(), { once: true });
    else initMediaViewer();
}
