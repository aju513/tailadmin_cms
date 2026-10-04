import { test } from 'node:test';
import assert from 'node:assert/strict';
import { newsEditor, newsSlug } from '../../resources/admin/js/components/news-editor.js';

test('typing a news title generates the slug and SEO title', () => {
    const state = newsEditor();
    state.init();
    state.updateTitle('Council Meeting 2026!');
    assert.equal(state.slug, 'council-meeting-2026');
    assert.equal(state.seoTitle, 'Council Meeting 2026!');
    state.updateTitle('Next meeting');
    assert.equal(state.slug, 'next-meeting');
    assert.equal(state.seoTitle, 'Next meeting');
});

test('manual edits survive later title changes and validation reloads', () => {
    const state = newsEditor({ title: 'Original title' });
    state.init();
    state.updateSlug('custom-address');
    state.updateSeoTitle('Custom search headline');
    state.updateTitle('Revised title');
    assert.equal(state.slug, 'custom-address');
    assert.equal(state.seoTitle, 'Custom search headline');

    const restored = newsEditor({ title: state.title, slug: state.slug, seoTitle: state.seoTitle });
    restored.init();
    restored.updateTitle('Another revision');
    assert.equal(restored.slug, 'custom-address');
    assert.equal(restored.seoTitle, 'Custom search headline');
});

test('editing existing news preserves its URL and updates an automatic SEO title', () => {
    const state = newsEditor({
        title: 'Original title', originalTitle: 'Original title',
        slug: 'original-title', seoTitle: 'Original title', existing: true,
    });
    state.init();
    state.updateTitle('Revised title');
    assert.equal(state.slug, 'original-title');
    assert.equal(state.seoTitle, 'Revised title');

    state.updateSeoTitle('Edited search headline');
    state.updateTitle('Final title');
    assert.equal(state.seoTitle, 'Edited search headline');
});

test('blank fields and unchanged suggestions resume automatic generation', () => {
    const state = newsEditor({ title: 'First title', slug: 'first-title', seoTitle: 'First title' });
    state.init();
    state.updateTitle('Second title');
    assert.equal(state.slug, 'second-title');
    assert.equal(state.seoTitle, 'Second title');
    state.updateSlug('custom');
    state.updateSeoTitle('Custom');
    state.updateSlug('');
    state.updateSeoTitle('');
    state.updateTitle('Third title');
    assert.equal(state.slug, 'third-title');
    assert.equal(state.seoTitle, 'Third title');
});

test('slug suggestions handle accents, punctuation, separators, and empty aliases', () => {
    assert.equal(newsSlug('Café @ Council__2026 -- Update!'), 'cafe-at-council-2026-update');
    assert.equal(newsSlug('  Council & Residents  '), 'council-residents');
    assert.equal(newsSlug('!!!'), '');
});
