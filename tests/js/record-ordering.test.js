import { test } from 'node:test';
import assert from 'node:assert/strict';
import { recordOrdering } from '../../resources/admin/js/components/record-ordering.js';

function fixture(label = 'Video', canReorder = true) {
    const rows = ['1', '2', '3'].map(id => ({ dataset: { recordId: id }, classList: { add() {}, remove() {} }, getBoundingClientRect: () => ({ top: 0, height: 20 }) }));
    const root = {
        querySelectorAll: () => [...rows],
        insertBefore(row, adjacent) {
            rows.splice(rows.indexOf(row), 1);
            rows.splice(adjacent ? rows.indexOf(adjacent) : rows.length, 0, row);
        },
        appendChild(row) { this.insertBefore(row, null); },
    };
    rows.forEach(row => {
        row.parentNode = root;
        Object.defineProperty(row, 'nextSibling', { get: () => rows[rows.indexOf(row) + 1] ?? null });
    });
    const state = recordOrdering('/order', canReorder, { label });
    state.$refs = { rows: root };
    return { state, rows, ids: () => rows.map(row => row.dataset.recordId) };
}

async function withFetch(mock, run) {
    const oldFetch = globalThis.fetch, oldDocument = globalThis.document;
    globalThis.document = { querySelector: () => ({ content: 'csrf' }) };
    globalThis.fetch = mock;
    try { await run(); }
    finally { globalThis.fetch = oldFetch; globalThis.document = oldDocument; }
}

for (const label of ['Video', 'Gallery', 'Home slide']) {
    test(`${label} arrows submit page IDs with their original sequence`, async () => {
        const { state, rows, ids } = fixture(label);
        let payload;
        await withFetch(async (url, options) => {
            assert.equal(url, '/order');
            assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf');
            payload = JSON.parse(options.body);
            return { ok: true };
        }, async () => {
            await state.move(rows[0], 1);
            assert.deepEqual(ids(), ['2', '1', '3']);
            assert.deepEqual(payload, { records: ['2', '1', '3'], original_order: ['1', '2', '3'] });
            assert.equal(state.message, `${label} order updated.`);
            assert.equal(state.saving, false);
            assert.equal(state.failed, false);
        });
    });
}

test('rejected and network-failed moves restore the displayed order with feedback', async () => {
    for (const mock of [
        async () => ({ ok: false, json: async () => ({ errors: { records: ['The order changed. Reload before reordering.'] } }) }),
        async () => { throw new Error('Connection failed'); },
        async () => ({ ok: false, json: async () => { throw new Error('Invalid JSON'); } }),
    ]) {
        const { state, rows, ids } = fixture();
        await withFetch(mock, async () => {
            await state.move(rows[2], -1);
            assert.deepEqual(ids(), ['1', '2', '3']);
            assert.equal(state.failed, true);
            assert.equal(state.saving, false);
            assert.ok(state.message.length > 0);
        });
    }
});

test('arrows and dragging respect permissions edges and pending requests', async () => {
    const { state, rows, ids } = fixture('Gallery', false);
    let resolve, calls = 0;
    await withFetch(() => { calls++; return new Promise(done => { resolve = done; }); }, async () => {
        await state.move(rows[0], 1);
        assert.equal(calls, 0);
        state.canReorder = true;
        await state.move(rows[0], -1);
        await state.move(rows[2], 1);
        assert.equal(calls, 0);
        const saving = state.move(rows[0], 1);
        assert.equal(state.saving, true);
        await state.move(rows[2], -1);
        let prevented = false;
        state.start({ target: { closest: () => null }, preventDefault() { prevented = true; } });
        assert.equal(prevented, true);
        assert.equal(state.dragging, null);
        assert.deepEqual(ids(), ['2', '1', '3']);
        assert.equal(calls, 1);
        resolve({ ok: true });
        await saving;
        assert.equal(state.saving, false);
    });
});

test('dragging skips interactive fields and persists a dropped row', async () => {
    const { state, rows, ids } = fixture('Home slide');
    let payload, prevented = false;
    await withFetch(async (url, options) => { payload = JSON.parse(options.body); return { ok: true }; }, async () => {
        state.start({ target: { closest: () => true }, currentTarget: rows[0], preventDefault() { prevented = true; } });
        assert.equal(prevented, true);
        assert.equal(state.dragging, null);
        state.start({ target: { closest: () => null }, currentTarget: rows[0], dataTransfer: { setData() {} }, preventDefault() {} });
        await state.drop({ currentTarget: rows[2], clientY: 20, preventDefault() {} });
        assert.deepEqual(ids(), ['2', '3', '1']);
        assert.deepEqual(payload, { records: ['2', '3', '1'], original_order: ['1', '2', '3'] });
        assert.equal(state.dragging, null);
        assert.equal(state.message, 'Home slide order updated.');
    });
});
