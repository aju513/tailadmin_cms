export function newsSlug(title) {
    const letters = { 'ß': 'ss', 'æ': 'ae', 'œ': 'oe', 'ø': 'o', 'ł': 'l', 'đ': 'd' };

    return String(title)
        .toLowerCase()
        .normalize('NFKD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[ßæœøłđ]/g, letter => letters[letter])
        .replace(/_/g, '-')
        .replace(/@/g, '-at-')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

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
