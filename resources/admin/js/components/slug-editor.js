export function slugify(title) {
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

export function slugEditor(initial = {}) {
    const title = String(initial.title ?? '');
    const slug = String(initial.slug ?? '');

    return {
        title,
        slug,
        automaticSlug: !slug || (!initial.existing && slug === slugify(title)),

        init() {
            this.updateTitle(this.title);
        },

        updateTitle(value) {
            this.title = value;
            if (this.automaticSlug) this.slug = slugify(value);
        },

        updateSlug(value) {
            this.slug = value;
            this.automaticSlug = !value || value === slugify(this.title);
        },
    };
}
