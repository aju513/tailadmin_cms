import { test } from 'node:test';
import assert from 'node:assert/strict';
import { pageManager } from '../../resources/js/components/page-manager.js';

globalThis.document = { querySelector: () => ({ content: 'test-csrf' }) };

test('manager adapters send module-specific selection keys and normalize boolean statuses', async () => {
    for (const key of ['categories', 'members', 'news', 'notices', 'resources', 'halls', 'records', 'users']) {
        const manager = pageManager({ 1: '0', 2: '0' }, key);
        globalThis.fetch = async (url, options) => {
            assert.deepEqual(JSON.parse(options.body), { [key]: ['1'], status: '1' });
            return { ok: true, json: async () => ({ message: 'Saved.', records: [{ id: 1, status: 1 }] }) };
        };
        await manager.changeStatus('/bulk-status', 'PATCH', ['1'], '1');
        assert.deepEqual(manager.statuses, { 1: '1', 2: '0' });
        assert.equal(manager.statusError, false);
    }
});

test('status updates apply server state and preserve selections without navigation', async () => {
    const manager = pageManager({ 1: 'draft', 2: 'draft' });
    manager.selected = ['1'];
    globalThis.fetch = async (url, options) => {
        assert.equal(options.headers['X-CSRF-TOKEN'], 'test-csrf');
        assert.equal(options.headers.Accept, 'application/json');
        assert.deepEqual(JSON.parse(options.body), { pages: ['1'], status: 'published' });
        return { ok: true, json: async () => ({ message: 'Saved.', pages: [{ id: 1, status: 'published' }] }) };
    };
    await manager.changeStatus('/bulk-status', 'PATCH', ['1'], 'published');
    assert.deepEqual(manager.statuses, { 1: 'published', 2: 'draft' });
    assert.deepEqual(manager.selected, ['1']);
    assert.equal(manager.statusBusy, false);
});

test('failed requests preserve icon state and surface permission or session feedback', async () => {
    const manager = pageManager({ 1: 'draft' });
    for (const status of [403, 419]) {
        globalThis.fetch = async () => ({ ok: false, status, json: async () => ({}) });
        await manager.changeStatus('/publish', 'POST', ['1']);
        assert.equal(manager.statuses[1], 'draft');
        assert.equal(manager.statusError, true);
        assert.match(manager.statusMessage, status === 403 ? /permission/ : /session expired/);
        assert.equal(manager.statusBusy, false);
    }
});

test('pending request prevents duplicate submits and clears loading after network failure', async () => {
    const manager = pageManager({ 1: 'draft' });
    let rejectRequest;
    let calls = 0;
    globalThis.fetch = () => {
        calls++;
        return new Promise((resolve, reject) => { rejectRequest = reject; });
    };
    const pending = manager.changeStatus('/publish', 'POST', ['1']);
    assert.deepEqual(manager.pendingIds, ['1']);
    await manager.changeStatus('/publish', 'POST', ['1']);
    assert.equal(calls, 1);
    rejectRequest(new Error('Network unavailable'));
    await pending;
    assert.equal(manager.statuses[1], 'draft');
    assert.equal(manager.statusBusy, false);
    assert.deepEqual(manager.pendingIds, []);
});
