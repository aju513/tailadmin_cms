export function initWebsiteSearch(doc = document) {
    const controls = [...doc.querySelectorAll('.websearch-wrap')].map(wrapper => ({
        wrapper, trigger: wrapper.querySelector('.search-btn'), panel: wrapper.querySelector('.search-box-elements'),
    })).filter(control => control.trigger && control.panel);

    function close(control, restoreFocus = false) {
        control.panel.hidden = true;
        control.trigger.setAttribute('aria-expanded', 'false');
        if (restoreFocus) control.trigger.focus();
    }

    controls.forEach(control => {
        control.trigger.addEventListener('click', () => {
            const opening = control.panel.hidden;
            controls.forEach(other => close(other));
            if (opening) {
                control.panel.hidden = false;
                control.trigger.setAttribute('aria-expanded', 'true');
                control.panel.querySelector('input[type="search"]')?.focus();
            }
        });
        control.wrapper.querySelector('.search-close')?.addEventListener('click', () => close(control, true));
        control.wrapper.addEventListener('focusout', event => {
            if (!control.wrapper.contains(event.relatedTarget)) close(control);
        });
    });
    doc.addEventListener('click', event => controls.forEach(control => {
        if (!control.wrapper.contains(event.target)) close(control);
    }));
    doc.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        controls.forEach(control => {
            if (!control.panel.hidden) close(control, control.wrapper.contains(doc.activeElement));
        });
    });
}
