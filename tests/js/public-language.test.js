import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initLanguageSwitcher } from '../../resources/front/js/language-switcher.js';

function element(dataset = {}) {
    const listeners = {}, attributes = {}, classes = new Set();
    return { dataset, attributes, classes, hidden: true,
        classList: { toggle: (key, enabled) => enabled ? classes.add(key) : classes.delete(key), add: key => classes.add(key) },
        addEventListener: (event, callback) => { listeners[event] = callback; },
        setAttribute: (name, value) => { attributes[name] = value; },
        click: () => listeners.click?.(),
    };
}

function fixture({ mode = 'gtranslate', saved = null, query = '', cookie = '', delay = 0, available = true } = {}) {
    const options = ['en', 'ne'].map(language => element({ language, flag: `${language}.svg` }));
    const toggle = element(), menu = element(), flag = element(), feedback = element(), input = element();
    const clicks = [], persisted = {}, timers = [];
    let ready = delay === 0;
    const switcher = { querySelectorAll: () => options, querySelector: selector => ({ '.language-switcher__toggle': toggle, '.language-switcher__menu': menu, '[data-current-language-flag]': flag })[selector] };
    const doc = {
        cookie, documentElement: { dataset: { translationMode: mode }, lang: 'en' },
        querySelectorAll: selector => selector === '[data-language-switcher]' ? [switcher] : [input],
        querySelector: selector => selector === '[data-language-feedback]' ? feedback : available && ready ? { click: () => clicks.push(selector.includes('"ne"') ? 'ne' : 'en') } : null,
    };
    const win = {
        location: { href: `https://site.example/videos${query}`, assign: value => { win.destination = value; } },
        localStorage: { getItem: () => saved, setItem: (key, value) => { persisted[key] = value; } },
        setTimeout: callback => { timers.push(callback); },
    };
    const state = initLanguageSwitcher(doc, win);
    const tick = async () => { if (--delay <= 0) ready = true; timers.splice(0).forEach(callback => callback()); await Promise.resolve(); };
    return { state, doc, win, options, toggle, menu, flag, feedback, input, clicks, persisted, tick };
}

test('language flags wait for the widget, persist Nepali and restore English without navigating', async () => {
    const f = fixture({ saved: 'ne', delay: 2 });
    assert.deepEqual(f.clicks, []);
    await f.tick(); await f.tick();
    assert.deepEqual(f.clicks, ['ne']);
    assert.equal(f.doc.documentElement.lang, 'ne');
    assert.equal(f.flag.src, 'ne.svg');
    assert.equal(f.persisted['pcgg-language'], 'ne');
    assert.ok(f.input.classes.has('notranslate'));
    f.toggle.click(); assert.equal(f.menu.hidden, false);
    f.options[0].click();
    assert.equal(f.menu.hidden, true);
    assert.deepEqual(f.clicks, ['ne', 'en']);
    assert.equal(f.doc.documentElement.lang, 'en');
    assert.equal(f.win.destination, undefined);
});

test('explicit language takes precedence over storage and the translation cookie', () => {
    const f = fixture({ saved: 'ne', cookie: 'googtrans=/en/ne', query: '?q=training&lang=en' });
    assert.deepEqual(f.clicks, ['en']);
    assert.equal(f.options[0].attributes['aria-pressed'], 'true');
});

test('new selections cancel stale requests while the widget loads', async () => {
    const f = fixture({ saved: 'ne', delay: 2 });
    f.options[0].click();
    await f.tick(); await f.tick();
    assert.deepEqual(f.clicks, ['en']);
});

test('unavailable translation announces a failure and can recover on another attempt', async () => {
    const f = fixture({ delay: 100 });
    for (let i = 0; i < 40; i++) await f.tick();
    assert.equal(f.feedback.hidden, false);
    assert.match(f.feedback.textContent, /Translation is unavailable/);
    for (let i = 0; i < 60; i++) await f.tick();
    assert.equal(await f.state.apply('ne'), true);
    assert.equal(f.feedback.hidden, true);
});

test('manual mode keeps the query and uses the existing language URL', () => {
    const f = fixture({ mode: 'manual', query: '?q=training&page=2' });
    f.options[1].click();
    assert.equal(f.win.destination, 'https://site.example/videos?q=training&page=2&lang=ne');
    assert.deepEqual(f.clicks, []);
});
