import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initMediaViewer } from '../../resources/front/js/media-viewer.js';

test('homepage gallery keeps its main image and viewer aligned with the selected thumbnail', () => {
    const thumbnails = ['first', 'second'].map(name => {
        let clicked, active;
        return { href: `https://site.example/${name}.jpg`, dataset: { galleryAlt: name },
            classList: { toggle: (key, value) => { active = value; } },
            addEventListener: (event, callback) => { clicked = callback; },
            click: () => clicked(), isActive: () => active,
        };
    });
    let open, removed, bound;
    const main = { removeAttribute: value => { removed = value; } };
    const opener = { addEventListener: (event, callback) => { open = callback; } };
    const doc = { querySelectorAll: () => thumbnails, querySelector: () => opener, getElementById: () => main };
    initMediaViewer(doc, { bind: selector => { bound = selector; } });
    thumbnails[1].click();
    assert.equal(bound, '[data-fancybox]');
    assert.equal(main.src, thumbnails[1].href);
    assert.equal(main.alt, 'second');
    assert.equal(removed, 'srcset');
    assert.equal(opener.href, thumbnails[1].href);
    assert.equal(thumbnails[1].isActive(), true);
    assert.equal(thumbnails[0].isActive(), false);
    let prevented = false;
    open({ preventDefault: () => { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(main.src, thumbnails[1].href);
});

test('media initialization tolerates pages without a homepage gallery', () => {
    const doc = { querySelectorAll: () => [], querySelector: () => null, getElementById: () => null };
    assert.doesNotThrow(() => initMediaViewer(doc, { bind() {} }));
    assert.doesNotThrow(() => initMediaViewer(doc, {}));
});
