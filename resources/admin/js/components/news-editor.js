import { slugify as newsSlug } from './slug-editor.js';

export { newsSlug };

export function newsEditor(initial = {}) {
    const title = String(initial.title ?? '');
    const slug = String(initial.slug ?? '');
    const seoTitle = String(initial.seoTitle ?? '');

    return {
        title,
        slug,
        seoTitle,
        activeImage: initial.activeImage ?? 'banner',
        automaticSlug: !slug || (!initial.existing && slug === newsSlug(title)),
        automaticSeoTitle: !seoTitle || seoTitle === title || seoTitle === initial.originalTitle,

        init() {
            this.updateTitle(this.title);
        },

        updateTitle(value) {
            this.title = value;
            if (this.automaticSlug) this.slug = newsSlug(value);
            if (this.automaticSeoTitle) this.seoTitle = value;
        },

        updateSlug(value) {
            this.slug = value;
            this.automaticSlug = !value || value === newsSlug(this.title);
        },

        updateSeoTitle(value) {
            this.seoTitle = value;
            this.automaticSeoTitle = !value || value === this.title;
        },
    };
}
