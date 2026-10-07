import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/front/js/scroll-reveal.js', import.meta.url), 'utf8');
function fixture(reduced = false) {
    const events = {}, motion = { matches: reduced, addEventListener(type, callback) { events.motion = callback; } };
    const animations = [];
    const group = {};
    const targets = Array.from({ length: 2 }, () => ({
        parentElement: { closest() { return null; } },
        closest(selector) { return selector.includes('.header') ? null : null; },
        getClientRects() { return [1]; },
        animate(frames, options) {
            const animation = { frames, options, effect: { target: this }, finished: new Promise(() => {}), finish() { this.finishedNow = true; }, cancel() { this.cancelled = true; } };
            animations.push(animation);
            return animation;
        },
        contains(target) { return target === this; },
    }));
    targets.forEach(target => { target.parentElement = group; });
    group.closest = () => null;
    const doc = { addEventListener(type, callback) { events[type] = callback; }, querySelectorAll() { return targets; } };
    let observer;
    class Observer {
        constructor(callback) { this.callback = callback; this.observed = []; observer = this; }
        observe(target) { this.observed.push(target); }
        unobserve(target) { this.observed = this.observed.filter(item => item !== target); }
        disconnect() { this.disconnected = true; }
    }
    runInNewContext(source, { document: doc, window: { matchMedia: () => motion, IntersectionObserver: Observer, addEventListener(type, callback) { events[type] = callback; } }, IntersectionObserver: Observer, Element: { prototype: { animate() {} } } });
    events.DOMContentLoaded();
    return { events, targets, observer, animations };
}

test('reveals run once on intersection and immediately finish for keyboard focus', () => {
    const { observer, targets, animations, events } = fixture();
    observer.callback(targets.map(target => ({ target, isIntersecting: false })));
    assert.equal(animations.length, 0);
    observer.callback(targets.map(target => ({ target, isIntersecting: true })));
    assert.equal(observer.observed.length, 0);
    assert.equal(animations[0].options.duration, 600);
    assert.equal(animations[1].options.delay, 90);
    events.focusin({ target: targets[1] });
    assert.equal(animations[1].finishedNow, true);
    assert.equal(animations[0].finishedNow, undefined);
    events.motion({ matches: true });
    assert.equal(observer.disconnected, true);
    assert.ok(animations.every(animation => animation.cancelled));
});

test('reduced motion skips reveal setup and printing cancels active animations', () => {
    assert.equal(fixture(true).observer, undefined);
    const { observer, targets, animations, events } = fixture();
    observer.callback([{ target: targets[0], isIntersecting: true }]);
    events.beforeprint();
    assert.equal(observer.disconnected, true);
    assert.equal(animations[0].cancelled, true);
});
