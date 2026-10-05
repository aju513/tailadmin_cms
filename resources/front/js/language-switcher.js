export function initLanguageSwitcher(doc = document, win = window) {
    const switchers = [...doc.querySelectorAll('[data-language-switcher]')];
    if (!switchers.length) return;
    const automatic = doc.documentElement.dataset.translationMode === 'gtranslate';
    const feedback = doc.querySelector('[data-language-feedback]');
    const allowed = (value) => ['en', 'ne'].includes(value);
    const url = new URL(win.location.href);
    let saved;
    try { saved = win.localStorage.getItem('pcgg-language'); } catch { /* Storage may be disabled. */ }
    const cookie = doc.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/)?.[1];
    let cookieLanguage;
    try { cookieLanguage = decodeURIComponent(cookie || '').split('/').pop(); } catch { /* Ignore malformed cookies. */ }
    const initial = [url.searchParams.get('lang'), saved, cookieLanguage].find(allowed) || 'en';
    let generation = 0;

    const reflect = (language) => {
        switchers.forEach((switcher) => {
            switcher.querySelectorAll('[data-language-option]').forEach((option) => {
                const selected = option.dataset.language === language;
                option.classList.toggle('is-selected', selected);
                option.setAttribute('aria-pressed', String(selected));
                if (selected) {
                    const flag = switcher.querySelector('[data-current-language-flag]');
                    if (flag) flag.src = option.dataset.flag;
                    switcher.querySelector('.language-switcher__toggle')?.setAttribute('aria-label', `Choose language. Current: ${language === 'ne' ? 'नेपाली' : 'English'}`);
                }
            });
        });
        doc.documentElement.lang = language;
    };
    const close = () => switchers.forEach((switcher) => {
        const menu = switcher.querySelector('.language-switcher__menu');
        if (menu) menu.hidden = true;
        switcher.querySelector('.language-switcher__toggle')?.setAttribute('aria-expanded', 'false');
    });
    const apply = async (language) => {
        const current = ++generation;
        if (feedback) feedback.hidden = true;
        for (let attempt = 0; attempt <= 40; attempt++) {
            if (current !== generation) return false;
            const option = doc.querySelector(`.gtranslate_wrapper [data-gt-lang="${language}"]`);
            if (option) {
                option.click();
                reflect(language);
                try { win.localStorage.setItem('pcgg-language', language); } catch { /* Keep switching without storage. */ }
                return true;
            }
            if (attempt < 40) await new Promise((resolve) => win.setTimeout(resolve, 250));
        }
        if (feedback) {
            feedback.textContent = 'Translation is unavailable. Please try choosing the language again.';
            feedback.hidden = false;
        }
        return false;
    };
    switchers.forEach((switcher) => {
        const toggle = switcher.querySelector('.language-switcher__toggle');
        const menu = switcher.querySelector('.language-switcher__menu');
        toggle?.addEventListener('click', () => {
            if (!menu) return;
            const open = menu.hidden;
            close();
            menu.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
        });
        switcher.querySelectorAll('[data-language-option]').forEach((option) => {
            option.addEventListener('click', () => {
                const language = option.dataset.language;
                if (!allowed(language)) return;
                close();
                if (automatic) void apply(language);
                else { url.searchParams.set('lang', language); win.location.assign(url.href); }
            });
        });
    });
    reflect(automatic ? 'en' : doc.documentElement.lang);
    if (automatic) {
        doc.querySelectorAll('input, textarea, select').forEach((field) => field.classList.add('notranslate'));
        void apply(initial);
    }
    return { apply };
}

if (typeof document !== 'undefined') initLanguageSwitcher();
