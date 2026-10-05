import { test } from 'node:test';
import assert from 'node:assert/strict';
import { capacityReportEditor } from '../../resources/admin/js/components/capacity-report-editor.js';

const editor = () => capacityReportEditor({ development: [{ key: 'Training', value: 0 }, { key: 'Participants', value: 12 }], collaboration: [{ key: 'Studies', value: null }], maxRows: 3 });

test('reordering keeps each metric value and validation feedback with its key', () => {
    const state = editor();
    state.development[0].valueError = 'Invalid value';
    const firstId = state.development[0].uid;
    state.move('development', 0, 1);
    assert.deepEqual(state.development.map(row => [row.key, row.value]), [['Participants', 12], ['Training', 0]]);
    assert.equal(state.development[1].uid, firstId);
    assert.equal(state.development[1].valueError, 'Invalid value');
    state.move('development', 1, 1);
    assert.equal(state.development[1].key, 'Training');
});

test('removal preserves remaining row identity and retains at least one entry per card', () => {
    const state = editor();
    const participant = state.development[1];
    state.remove('development', 0);
    assert.equal(state.development[0], participant);
    state.remove('development', 0);
    state.remove('collaboration', 0);
    assert.equal(state.development.length, 1);
    assert.equal(state.collaboration.length, 1);
});

test('adding entries respects the limit and focuses unique controls across both cards', () => {
    const state = editor();
    let focused = '';
    const previousDocument = globalThis.document;
    globalThis.document = { getElementById: id => ({ focus() { focused = id; } }) };
    state.$nextTick = callback => callback();
    try {
        state.add('development');
        assert.equal(focused, `metric-development-${state.development[2].uid}-key`);
        state.add('development');
        assert.equal(state.development.length, 3);
        state.add('collaboration');
        assert.equal(focused, `metric-collaboration-${state.collaboration[1].uid}-key`);
        const ids = [...state.development, ...state.collaboration].map(row => row.uid);
        assert.equal(new Set(ids).size, ids.length);
    } finally {
        globalThis.document = previousDocument;
    }
});
