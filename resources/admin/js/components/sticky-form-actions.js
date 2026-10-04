export function stickyFormActions(formId) {
    return {
        stickyActions: false,
        observer: null,

        init() {
            const ownerDocument = this.$el?.ownerDocument;
            const save = ownerDocument?.getElementById(`${formId}-save`);
            const form = ownerDocument?.getElementById(formId);
            if (!save || !form || save.form !== form) return;

            this.observer = new IntersectionObserver(([entry]) => {
                this.stickyActions = !entry.isIntersecting && entry.boundingClientRect.bottom <= 80;
            }, { rootMargin: '-80px 0px 0px 0px' });
            this.observer.observe(save);
        },

        destroy() {
            this.observer?.disconnect();
        },
    };
}
