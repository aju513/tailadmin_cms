import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initHeaderLayout } from '../../resources/front/js/header-layout.js';

function fixture() {
    const nav = (height) => {
        const classes = new Set();
        return { offsetHeight: height, classList: {
            contains: name => classes.has(name),
            remove: name => classes.delete(name),
            toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name),
        } };
    };
    const desktop = nav(48), mobile = nav(80);
    const spacer = { style: { height: '0px' }, get offsetHeight() { return parseFloat(this.style.height); } };
    const win = { innerWidth: 1280, scrollY: 0, addEventListener() {} };
    const header = { get offsetHeight() {
        return win.innerWidth >= 1024
            ? 96 + (desktop.classList.contains('sticky') ? 0 : desktop.offsetHeight)
            : (mobile.classList.contains('sticky') ? 0 : mobile.offsetHeight);
    } };
    const properties = {};
    const doc = {
        querySelector: selector => ({ '.header': header, '.header .header__menu': desktop, '.mob-nav': mobile, '.header-height': spacer })[selector],
        documentElement: { style: { setProperty: (name, value) => { properties[name] = value; } } },
    };
    const layout = initHeaderLayout(doc, win);
    return { desktop, mobile, spacer, win, properties, layout };
}

test('desktop stickiness reserves the original row even when its fixed height differs', () => {
    const f = fixture();
    assert.equal(f.properties['--site-header-height'], '144px');
    f.win.scrollY = 301;
    f.layout.update();
    f.desktop.offsetHeight = 52;
    f.layout.update();
    assert.equal(f.spacer.style.height, '48px');
    assert.equal(f.properties['--site-header-height'], '144px');
    f.win.scrollY = 0;
    f.layout.update();
    assert.equal(f.spacer.style.height, '0px');
    assert.equal(f.properties['--site-header-height'], '148px');
});

test('mobile scrolling and breakpoint changes retain space and reset inactive navigation', () => {
    const f = fixture();
    f.win.innerWidth = 640;
    f.layout.update();
    f.win.scrollY = 121;
    f.layout.update();
    assert.equal(f.mobile.classList.contains('sticky'), true);
    assert.equal(f.spacer.style.height, '80px');
    assert.equal(f.properties['--site-header-height'], '80px');
    f.win.innerWidth = 1280;
    f.layout.update();
    assert.equal(f.mobile.classList.contains('sticky'), false);
    assert.equal(f.spacer.style.height, '0px');
    assert.equal(f.properties['--site-header-height'], '144px');
});

test('font-driven navigation height changes update the hero offset before scrolling', () => {
    const f = fixture();
    f.desktop.offsetHeight = 56;
    f.layout.update();
    assert.equal(f.properties['--site-header-height'], '152px');
    f.win.scrollY = 400;
    f.layout.update();
    assert.equal(f.spacer.style.height, '56px');
    assert.equal(f.properties['--site-header-height'], '152px');
});
