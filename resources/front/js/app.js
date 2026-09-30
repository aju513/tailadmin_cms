import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade, A11y } from 'swiper/modules';
import '../vendor/fancybox.js';

// Keep the supplied design's selectors and interactions.
const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
document.querySelectorAll('.homepage-banner-swiper').forEach((element) => {
    const multiple = element.querySelectorAll('.swiper-slide').length > 1;
    new Swiper(element, { modules: [Pagination, Autoplay, EffectFade, A11y], slidesPerView: 1, loop: multiple, effect: 'fade', fadeEffect: { crossFade: true }, autoplay: multiple && !reducedMotion ? { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true } : false, pagination: { el: element.querySelector('.homepage__banner-pagination'), clickable: true } });
});
document.querySelectorAll('.homepage-about-award-swiper').forEach((element) => {
    const media = element.closest('.homepage__about-media');
    new Swiper(element, { modules: [Navigation, A11y], slidesPerView: 3, spaceBetween: 23, navigation: { nextEl: media.querySelector('.homepage__about-award-next'), prevEl: media.querySelector('.homepage__about-award-prev') } });
});
document.querySelectorAll('.homepage__about-award-item[data-gallery-image]').forEach((thumbnail) => {
    thumbnail.addEventListener('click', () => {
        const image = document.getElementById('homepage-about-main-image');
        if (!image || !thumbnail.dataset.galleryImage) return;
        image.src = thumbnail.dataset.galleryImage;
        image.removeAttribute('srcset');
        image.alt = thumbnail.dataset.galleryAlt || '';
        document.querySelectorAll('.homepage__about-award-item').forEach((item) => { item.classList.toggle('is-active', item === thumbnail); item.setAttribute('aria-pressed', String(item === thumbnail)); });
    });
});
document.querySelectorAll('.homepage__resources .resource-swiper').forEach((element) => {
    const panel = element.closest('[role="tabpanel"]');
    new Swiper(element, { modules: [Navigation, A11y], slidesPerView: 1, spaceBetween: 20, navigation: { nextEl: panel.querySelector('.resource-next'), prevEl: panel.querySelector('.resource-prev') }, breakpoints: { 640: { slidesPerView: 2 } } });
});
document.querySelectorAll('[data-resource-tabs]').forEach((library) => {
    const tabs = [...library.querySelectorAll('[data-resource-tab]')];
    const panels = [...library.querySelectorAll('[data-resource-panel]')];
    const activate = (tab) => {
        tabs.forEach((item) => { item.classList.toggle('is-active', item === tab); item.setAttribute('aria-selected', String(item === tab)); item.tabIndex = item === tab ? 0 : -1; });
        panels.forEach((panel) => { panel.hidden = panel.dataset.resourcePanel !== tab.dataset.resourceTab; if (!panel.hidden) panel.querySelector('.resource-swiper')?.swiper?.update(); });
    };
    tabs.forEach((tab, index) => {
        tab.tabIndex = index === 0 ? 0 : -1;
        tab.addEventListener('click', () => activate(tab));
        tab.addEventListener('keydown', (event) => {
            const positions = { ArrowRight: (index + 1) % tabs.length, ArrowLeft: (index + tabs.length - 1) % tabs.length, Home: 0, End: tabs.length - 1 };
            if (!(event.key in positions)) return;
            event.preventDefault();
            const next = tabs[positions[event.key]];
            next.focus();
            activate(next);
        });
    });
});
document.querySelectorAll('[data-report-year]').forEach((button) => button.addEventListener('click', () => {
    document.querySelectorAll('[data-report-year]').forEach((item) => { item.classList.toggle('is-active', item === button); item.setAttribute('aria-pressed', String(item === button)); });
    document.querySelectorAll('[data-report-panel]').forEach((panel) => { panel.hidden = panel.dataset.reportPanel !== button.dataset.reportYear; });
}));

const header = document.querySelector('header.header');
const spacer = document.querySelector('.header-height');
const mobileNav = document.querySelector('.mob-nav');
const mobileMenu = document.querySelector('.mob-nav .overflow > ul');
const menuToggle = document.getElementById('menu-toggle');
const stickyHeader = () => {
    const desktop = innerWidth >= 1024;
    const sticky = desktop && scrollY > 300;
    header?.classList.toggle('sticky', sticky);
    if (spacer) spacer.style.height = sticky ? '59px' : '0';
    mobileNav?.classList.toggle('sticky', !desktop && scrollY > 120);
};
addEventListener('scroll', stickyHeader, { passive: true });
addEventListener('resize', stickyHeader);
stickyHeader();
menuToggle?.addEventListener('click', () => {
    const open = menuToggle.getAttribute('aria-expanded') !== 'true';
    menuToggle.setAttribute('aria-expanded', String(open));
    mobileMenu?.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
});
menuToggle?.addEventListener('keydown', (event) => { if (['Enter', ' '].includes(event.key)) { event.preventDefault(); menuToggle.click(); } });
document.querySelectorAll('.open-menu').forEach((button) => button.addEventListener('click', () => {
    const submenu = button.nextElementSibling;
    if (!submenu) return;
    const open = button.getAttribute('aria-expanded') !== 'true';
    submenu.style.maxHeight = open ? '10000px' : '0';
    button.classList.toggle('rotate', open);
    button.setAttribute('aria-expanded', String(open));
}));
document.querySelectorAll('.open-menu').forEach((button) => button.addEventListener('keydown', (event) => { if (['Enter', ' '].includes(event.key)) { event.preventDefault(); button.click(); } }));
const closeDropdowns = () => {
    document.querySelectorAll('.header .dropdown').forEach((dropdown) => dropdown.classList.add('hidden'));
    document.querySelectorAll('.dropdown-toggle').forEach((toggle) => { toggle.setAttribute('aria-expanded', 'false'); toggle.querySelector('.icon')?.classList.remove('rotate-180'); });
};
document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
    toggle.tabIndex = 0;
    toggle.setAttribute('role', 'button');
    toggle.setAttribute('aria-expanded', 'false');
    const activate = () => {
        const dropdown = toggle.nextElementSibling;
        const open = dropdown?.classList.contains('hidden');
        closeDropdowns();
        dropdown?.classList.toggle('hidden', !open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.icon')?.classList.toggle('rotate-180', open);
    };
    toggle.addEventListener('click', activate);
    toggle.addEventListener('keydown', (event) => { if (['Enter', ' '].includes(event.key)) { event.preventDefault(); activate(); } });
});
document.querySelectorAll('.websearch-wrap').forEach((wrapper) => {
    const box = wrapper.querySelector('.search-box-elements');
    wrapper.querySelector('.search-btn')?.addEventListener('click', () => { box?.classList.toggle('hidden'); if (!box?.classList.contains('hidden')) box?.querySelector('input')?.focus(); });
    wrapper.querySelector('.search-close')?.addEventListener('click', (event) => { event.preventDefault(); box?.classList.add('hidden'); });
});
document.getElementById('open-search')?.addEventListener('click', (event) => location.assign(event.currentTarget.dataset.searchUrl));
document.querySelectorAll('[data-language-switcher]').forEach((switcher) => {
    const toggle = switcher.querySelector('.language-switcher__toggle');
    const menu = switcher.querySelector('.language-switcher__menu');
    toggle?.addEventListener('click', () => { if (menu) { menu.hidden = !menu.hidden; toggle.setAttribute('aria-expanded', String(!menu.hidden)); } });
    switcher.querySelectorAll('[data-language-option]').forEach((option) => {
        const selected = option.dataset.language === document.documentElement.lang;
        option.classList.toggle('is-selected', selected);
        option.setAttribute('aria-pressed', String(selected));
        if (selected) { const flag = switcher.querySelector('[data-current-language-flag]'); if (flag) flag.src = option.dataset.flag; }
        option.addEventListener('click', () => { const url = new URL(location.href); url.searchParams.set('lang', option.dataset.language); location.assign(url); });
    });
});
document.querySelector('#news-category')?.addEventListener('change', (event) => event.currentTarget.form.requestSubmit());
const shareToggle = document.getElementById('newsShareToggle');
const shareMenu = document.getElementById('newsShareMenu');
shareToggle?.addEventListener('click', () => { const open = shareMenu?.classList.toggle('show'); shareToggle.setAttribute('aria-expanded', String(open)); });
document.querySelectorAll('[data-share-network]').forEach((button) => button.addEventListener('click', async () => {
    const url = document.querySelector('link[rel="canonical"]')?.href || location.href;
    const title = document.querySelector('.news-detail-page h1')?.textContent.trim() || document.title;
    const encoded = encodeURIComponent(url);
    const links = { facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + encoded, twitter: 'https://twitter.com/intent/tweet?url=' + encoded + '&text=' + encodeURIComponent(title), linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url=' + encoded, whatsapp: 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url) };
    if (button.dataset.shareNetwork === 'copy') { try { await navigator.clipboard.writeText(url); button.querySelector('span:last-child').textContent = 'Copied'; } catch { button.querySelector('span:last-child').textContent = 'Copy the page address'; } }
    else if (links[button.dataset.shareNetwork]) window.open(links[button.dataset.shareNetwork], '_blank', 'noopener,noreferrer');
}));
const toc = document.querySelector('.toc');
document.getElementById('toggleButton')?.addEventListener('click', () => document.getElementById('sidebar-toc')?.classList.toggle('active'));
if (toc) {
    const list = document.createElement('ul');
    list.className = 'toc-list';
    document.querySelectorAll('.js-toc-content h2, .js-toc-content h3').forEach((heading, index) => {
        heading.id ||= 'article-section-' + (index + 1);
        const item = document.createElement('li');
        const link = document.createElement('a');
        link.className = 'toc-link';
        link.href = '#' + heading.id;
        link.textContent = heading.textContent;
        item.append(link); list.append(item);
    });
    toc.replaceChildren(list);
}
document.addEventListener('click', (event) => {
    if (!event.target.closest('.dropdown, .dropdown-toggle')) closeDropdowns();
    if (!event.target.closest('#newsShareDropdown')) { shareMenu?.classList.remove('show'); shareToggle?.setAttribute('aria-expanded', 'false'); }
    if (!event.target.closest('[data-language-switcher]')) document.querySelectorAll('.language-switcher__menu').forEach((menu) => { menu.hidden = true; menu.previousElementSibling?.setAttribute('aria-expanded', 'false'); });
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    closeDropdowns();
    document.querySelectorAll('.search-box-elements').forEach((box) => box.classList.add('hidden'));
    document.querySelectorAll('.language-switcher__menu').forEach((menu) => { menu.hidden = true; });
    if (menuToggle?.getAttribute('aria-expanded') === 'true') menuToggle.click();
});
window.Fancybox?.bind('[data-fancybox]', {});
