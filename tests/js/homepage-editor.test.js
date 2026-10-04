import { test } from 'node:test';
import assert from 'node:assert/strict';
import { homepageEditor } from '../../resources/admin/js/components/homepage-editor.js';

test('homepage SEO title follows the welcome title until customized', () => {
    const state = homepageEditor({ title: 'Welcome' });
    state.init();
    assert.equal(state.seoTitle, 'Welcome');
    state.updateTitle('New welcome');
    assert.equal(state.seoTitle, 'New welcome');
    state.updateSeoTitle('Custom search title');
    state.updateTitle('Final welcome');
    assert.equal(state.seoTitle, 'Custom search title');
});

test('homepage validation reloads preserve custom SEO titles and active panels', () => {
    const state = homepageEditor({ title: 'Welcome', seoTitle: 'Edited SEO', activePanel: 'gallery', activeLanguage: 'ne' });
    state.init();
    state.updateTitle('Revised welcome');
    assert.equal(state.seoTitle, 'Edited SEO');
    assert.equal(state.activePanel, 'gallery');
    assert.equal(state.activeLanguage, 'ne');
});

test('homepage automatic SEO titles resume after clearing the field', () => {
    const state = homepageEditor({ title: 'Welcome', seoTitle: 'Welcome', originalTitle: 'Welcome' });
    state.init();
    state.updateTitle('Updated welcome');
    assert.equal(state.seoTitle, 'Updated welcome');
    state.updateSeoTitle('Custom');
    state.updateSeoTitle('');
    state.updateTitle('Final welcome');
    assert.equal(state.seoTitle, 'Final welcome');
});

test('invalid fields open the correct homepage panel and language', () => {
    const state = homepageEditor({ activePanel: 'seo', activeLanguage: 'ne' });
    state.revealInvalidField({ target: { closest: selector => selector === '[data-homepage-panel]' ? { dataset: { homepagePanel: 'content' } } : { dataset: { language: 'en' } } } });
    assert.equal(state.activePanel, 'content');
    assert.equal(state.activeLanguage, 'en');
});
