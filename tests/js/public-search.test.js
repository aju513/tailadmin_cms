import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initWebsiteSearch } from '../../resources/front/js/website-search.js';

function fixture() {
    const doc = { listeners: {}, addEventListener(type, callback) { this.listeners[type] = callback; } };
    function element() {
        return { listeners: {}, attributes: {}, addEventListener(type, callback) { this.listeners[type] = callback; },
            setAttribute(name, value) { this.attributes[name] = value; }, focus() { doc.activeElement = this; } };
    }
    const controls = [0, 1].map(() => {
        const trigger = element(), input = element(), close = element(), panel = element(), wrapper = element();
        panel.hidden = true;
        panel.querySelector = () => input;
        wrapper.querySelector = selector => ({ '.search-btn': trigger, '.search-box-elements': panel, '.search-close': close })[selector];
        wrapper.contains = target => [trigger, input, close, panel, wrapper].includes(target);
        return { trigger, input, close, panel, wrapper };
    });
    doc.querySelectorAll = () => controls.map(control => control.wrapper);
    initWebsiteSearch(doc);
    return { doc, controls };
}

test('opening a header search focuses its input and closes the other header search', () => {
    const { doc, controls: [normal, sticky] } = fixture();
    normal.trigger.listeners.click();
    assert.equal(normal.panel.hidden, false);
    assert.equal(normal.trigger.attributes['aria-expanded'], 'true');
    assert.equal(doc.activeElement, normal.input);
    sticky.trigger.listeners.click();
    assert.equal(normal.panel.hidden, true);
    assert.equal(normal.trigger.attributes['aria-expanded'], 'false');
    assert.equal(doc.activeElement, sticky.input);
    sticky.trigger.listeners.click();
    assert.equal(sticky.panel.hidden, true);
});

test('Escape and Close restore focus; outside clicks and leaving the search dismiss it', () => {
    const { doc, controls: [normal] } = fixture();
    normal.trigger.listeners.click();
    doc.listeners.keydown({ key: 'Escape' });
    assert.equal(normal.panel.hidden, true);
    assert.equal(doc.activeElement, normal.trigger);
    normal.trigger.listeners.click();
    normal.close.listeners.click();
    assert.equal(doc.activeElement, normal.trigger);
    normal.trigger.listeners.click();
    doc.listeners.click({ target: normal.input });
    assert.equal(normal.panel.hidden, false);
    doc.listeners.click({ target: {} });
    assert.equal(normal.panel.hidden, true);
    normal.trigger.listeners.click();
    normal.wrapper.listeners.focusout({ relatedTarget: normal.close });
    assert.equal(normal.panel.hidden, false);
    normal.wrapper.listeners.focusout({ relatedTarget: {} });
    assert.equal(normal.panel.hidden, true);
});
