const form = document.querySelector('[data-grievance-form]');
if (form) {
    const key = form.dataset.siteKey;
    const button = form.querySelector('button[type="submit"]');
    const feedback = form.querySelector('[data-grievance-error]');
    let pending = false;
    let loader;
    const ready = () => {
        if (loader) return loader;
        loader = new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('Security verification took too long. Please try again.')), 15000);
            const loaded = () => {
                if (!window.grecaptcha) { clearTimeout(timeout); reject(new Error('Security verification could not load. Please try again.')); return; }
                window.grecaptcha.ready(() => { clearTimeout(timeout); resolve(window.grecaptcha); });
            };
            if (window.grecaptcha) { loaded(); return; }
            const script = document.createElement('script');
            script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(key)}`;
            script.async = true;
            script.onload = loaded;
            script.onerror = () => { clearTimeout(timeout); reject(new Error('Security verification could not load. Please try again.')); };
            document.head.appendChild(script);
        }).catch((error) => { loader = null; throw error; });
        return loader;
    };
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (pending || !form.reportValidity()) return;
        pending = true;
        button.disabled = true;
        feedback.hidden = true;
        form.setAttribute('aria-busy', 'true');
        try {
            const recaptcha = await ready();
            let timeout;
            const token = await Promise.race([
                recaptcha.execute(key, { action: 'grievance_submit' }),
                new Promise((_, reject) => { timeout = setTimeout(() => reject(new Error('Security verification took too long. Please try again.')), 15000); }),
            ]).finally(() => clearTimeout(timeout));
            if (!token) throw new Error('Security verification failed. Please try again.');
            form.elements.recaptcha_token.value = token;
            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            feedback.textContent = 'Security verification failed. Please reload the page and try again.';
            feedback.hidden = false;
            pending = false;
            button.disabled = false;
            form.removeAttribute('aria-busy');
        }
    });
}
