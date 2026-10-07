export function initHeaderLayout(doc = document, win = window) {
    const header = doc.querySelector('.header');
    const desktopNav = doc.querySelector('.header .header__menu');
    const mobileNav = doc.querySelector('.mob-nav');
    const spacer = doc.querySelector('.header-height');
    if (!header || !spacer) return;
    const normalHeights = new Map();

    const update = () => {
        const desktop = win.innerWidth >= 1024;
        const activeNav = desktop ? desktopNav : mobileNav;
        const inactiveNav = desktop ? mobileNav : desktopNav;
        inactiveNav?.classList.remove('sticky');
        if (activeNav && !activeNav.classList.contains('sticky')) {
            normalHeights.set(activeNav, activeNav.offsetHeight);
        }
        const sticky = win.scrollY > (desktop ? 300 : 120);
        activeNav?.classList.toggle('sticky', sticky);
        spacer.style.height = sticky ? `${normalHeights.get(activeNav) || 0}px` : '0px';
        doc.documentElement.style.setProperty('--site-header-height', `${header.offsetHeight + spacer.offsetHeight}px`);
    };

    win.addEventListener('scroll', update, { passive: true });
    win.addEventListener('resize', update);
    update();
    if (win.ResizeObserver) {
        const observer = new win.ResizeObserver(update);
        [header, desktopNav, mobileNav].filter(Boolean).forEach((node) => observer.observe(node));
    }
    doc.fonts?.ready.then(update);
    return { update };
}
