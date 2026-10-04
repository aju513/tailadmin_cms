export function homepageEditor(initial = {}) {
    const title = String(initial.title ?? '');
    const seoTitle = String(initial.seoTitle ?? '');

    return {
        title,
        seoTitle,
        activePanel: initial.activePanel ?? 'content',
        activeLanguage: initial.activeLanguage ?? 'en',
        stickyActions: false,
        observer: null,
        automaticSeoTitle: !seoTitle || seoTitle === title || seoTitle === initial.originalTitle,

        init() {
            this.updateTitle(this.title);
            const save = this.$el?.ownerDocument.querySelector('[data-homepage-save]');
            if (!save) return;
            this.observer = new IntersectionObserver(([entry]) => {
                this.stickyActions = !entry.isIntersecting && entry.boundingClientRect.bottom <= 80;
            }, { rootMargin: '-80px 0px 0px 0px' });
            this.observer.observe(save);
        },

        destroy() {
            this.observer?.disconnect();
        },

        updateTitle(value) {
            this.title = value;
            if (this.automaticSeoTitle) this.seoTitle = value;
        },

        updateSeoTitle(value) {
            this.seoTitle = value;
            this.automaticSeoTitle = !value || value === this.title;
        },

        revealInvalidField(event) {
            this.activePanel = event.target.closest('[data-homepage-panel]')?.dataset.homepagePanel ?? 'content';
            this.activeLanguage = event.target.closest('[data-language]')?.dataset.language ?? 'en';
        },
    };
}
