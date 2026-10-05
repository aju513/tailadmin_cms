import { test } from 'node:test';
import assert from 'node:assert/strict';
import { resourceOrdering } from '../../resources/admin/js/components/resource-ordering.js';

function fixture(canReorder = true) {
    const rows = ['1', '2', '3'].map(id => ({ dataset: { resourceId: id }, classList: { add() {}, remove() {} }, getBoundingClientRect: () => ({ top: 0, height: 20 }) }));
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
    const state = resourceOrdering('/admin/resources/order', canReorder);
    state.$refs = { rows: root };
    return { state, rows, ids: () => rows.map(row => row.dataset.resourceId) };
}

test('arrow moves submit the new order and its original page sequence', async () => {
    const { state, rows, ids } = fixture();
    const oldFetch = globalThis.fetch, oldDocument = globalThis.document;
    let payload;
    globalThis.document = { querySelector: () => ({ content: 'csrf' }) };
    globalThis.fetch = async (url, options) => { payload = JSON.parse(options.body); return { ok: true }; };
    try {
        await state.move(rows[0], 1);
        assert.deepEqual(ids(), ['2', '1', '3']);
        assert.deepEqual(payload, { resources: ['2', '1', '3'], original_order: ['1', '2', '3'] });
        assert.equal(state.saving, false);
        assert.equal(state.failed, false);
        assert.equal(state.message, 'Resource order updated.');
    } finally { globalThis.fetch = oldFetch; globalThis.document = oldDocument; }
});

test('failed arrow requests restore the page and expose server validation feedback', async () => {
    const { state, rows, ids } = fixture();
    const oldFetch = globalThis.fetch, oldDocument = globalThis.document;
    globalThis.document = { querySelector: () => ({ content: 'csrf' }) };
    globalThis.fetch = async () => ({ ok: false, json: async () => ({ errors: { resources: ['The resource order changed. Reload before reordering.'] } }) });
    try {
        await state.move(rows[2], -1);
        assert.deepEqual(ids(), ['1', '2', '3']);
        assert.equal(state.failed, true);
        assert.equal(state.saving, false);
        assert.match(state.message, /Reload before reordering/);
    } finally { globalThis.fetch = oldFetch; globalThis.document = oldDocument; }
});

test('ordering respects permissions page edges and in flight writes', () => {
    const { state, rows, ids } = fixture(false);
    state.move(rows[0], 1);
    assert.deepEqual(ids(), ['1', '2', '3']);
    state.canReorder = true;
    state.move(rows[0], -1);
    state.move(rows[2], 1);
    state.saving = true;
    state.move(rows[0], 1);
    assert.deepEqual(ids(), ['1', '2', '3']);
});

test('drag drops persist row positions while excluding interactive fields', async () => {
    const { state, rows, ids } = fixture();
    const oldFetch = globalThis.fetch, oldDocument = globalThis.document;
    let payload;
    globalThis.document = { querySelector: () => ({ content: 'csrf' }) };
    globalThis.fetch = async (url, options) => { payload = JSON.parse(options.body); return { ok: true }; };
    let prevented = false;
    try {
        state.start({ target: { closest: () => true }, currentTarget: rows[0], preventDefault() { prevented = true; } });
        assert.equal(prevented, true);
        assert.equal(state.dragging, null);
        state.start({ target: { closest: () => null }, currentTarget: rows[0], dataTransfer: { setData() {} }, preventDefault() {} });
        await state.drop({ currentTarget: rows[1], clientY: 20, preventDefault() {} });
        assert.deepEqual(ids(), ['2', '1', '3']);
        assert.deepEqual(payload, { resources: ['2', '1', '3'], original_order: ['1', '2', '3'] });
        assert.equal(state.dragging, null);
        assert.equal(state.message, 'Resource order updated.');
    } finally { globalThis.fetch = oldFetch; globalThis.document = oldDocument; }
});
