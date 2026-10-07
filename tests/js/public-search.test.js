import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initWebsiteSearch } from '../../resources/front/js/website-search.js';

function fixture() {
    const doc = { body: { style: { overflow: 'auto' } } };
    const element = () => ({ listeners: {}, addEventListener(type, callback) { this.listeners[type] = callback; }, focus() { doc.activeElement = this; } });
    const input = element(), close = element(), dialog = element();
    const triggers = [element(), element()];
    dialog.open = false;
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => { dialog.open = false; dialog.listeners.close(); };
    dialog.querySelector = selector => selector === '.site-search-close' ? close : input;
    doc.getElementById = () => dialog;
    doc.querySelectorAll = () => triggers;
    initWebsiteSearch(doc);
    return { doc, dialog, input, close, triggers };
}

test('desktop and mobile triggers open the same modal, focus search, and lock scrolling', () => {
    const { doc, dialog, input, close, triggers } = fixture();
    for (const trigger of triggers) {
        trigger.listeners.click();
        assert.equal(dialog.open, true);
        assert.equal(doc.activeElement, input);
        assert.equal(doc.body.style.overflow, 'hidden');
        close.listeners.click();
        assert.equal(dialog.open, false);
        assert.equal(doc.activeElement, trigger);
        assert.equal(doc.body.style.overflow, 'auto');
    }
});

test('backdrop and native dismissal restore the opener and original scroll state', () => {
    const { doc, dialog, input, triggers } = fixture();
    triggers[0].listeners.click();
    triggers[1].listeners.click();
    dialog.listeners.click({ target: input });
    assert.equal(dialog.open, true);
    dialog.listeners.click({ target: dialog });
    assert.equal(dialog.open, false);
    assert.equal(doc.activeElement, triggers[0]);
    assert.equal(doc.body.style.overflow, 'auto');
    triggers[1].listeners.click();
    dialog.close();
    assert.equal(doc.activeElement, triggers[1]);
    assert.equal(doc.body.style.overflow, 'auto');
});
