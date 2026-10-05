import { test } from 'node:test';
import assert from 'node:assert/strict';
import { slugEditor, slugify } from '../../resources/admin/js/components/slug-editor.js';

test('titles and category names generate editable URLs as each input changes', () => {
    const state = slugEditor();
    state.init();
    state.updateTitle('Training & Capacity 2026');
    assert.equal(state.slug, 'training-capacity-2026');
    state.updateTitle('Training materials');
    assert.equal(state.slug, 'training-materials');
});

test('new forms initialize suggestions from a prefilled source', () => {
    const state = slugEditor({ title: 'Annual contribution' });
    state.init();
    assert.equal(state.slug, 'annual-contribution');
});

test('manual URLs survive later title changes and failed validation reloads', () => {
    const state = slugEditor({ title: 'Training materials' });
    state.init();
    state.updateSlug('our-custom-address');
    state.updateTitle('Updated materials');
    assert.equal(state.slug, 'our-custom-address');
    const restored = slugEditor({ title: state.title, slug: state.slug });
    restored.init();
    restored.updateTitle('Final title');
    assert.equal(restored.slug, 'our-custom-address');
});

test('generated suggestions resume after validation when creating a record', () => {
    const state = slugEditor({ title: 'Original title', slug: 'original-title', existing: false });
    state.init();
    state.updateTitle('Revised title');
    assert.equal(state.slug, 'revised-title');
});

test('existing URLs remain stable even when they match the original title', () => {
    const state = slugEditor({ title: 'Original title', slug: 'original-title', existing: true });
    state.init();
    state.updateTitle('Revised title');
    assert.equal(state.slug, 'original-title');
});

test('clearing a URL or restoring its suggestion resumes live generation', () => {
    const state = slugEditor({ title: 'Original title', slug: 'saved-url', existing: true });
    state.init();
    state.updateSlug('');
    state.updateTitle('Second title');
    assert.equal(state.slug, 'second-title');
    state.updateSlug('custom-url');
    state.updateSlug('second-title');
    state.updateTitle('Third title');
    assert.equal(state.slug, 'third-title');
});

test('independent forms do not share source or URL state', () => {
    const automatic = slugEditor({ title: 'First form' });
    const manual = slugEditor({ title: 'Second form', slug: 'custom-url' });
    automatic.init();
    manual.init();
    automatic.updateTitle('Changed title');
    assert.equal(automatic.slug, 'changed-title');
    assert.equal(manual.title, 'Second form');
    assert.equal(manual.slug, 'custom-url');
});

test('suggestions normalize accents separators and punctuation and clear with the title', () => {
    assert.equal(slugify('Café @ Council__2026 -- Update!'), 'cafe-at-council-2026-update');
    assert.equal(slugify('Straße & Æsir'), 'strasse-aesir');
    assert.equal(slugify('!!!'), '');
    const state = slugEditor({ title: 'Some title' });
    state.init();
    state.updateTitle('');
    assert.equal(state.slug, '');
});
