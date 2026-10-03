import { test } from 'node:test';
import assert from 'node:assert/strict';
import { multiselect } from '../../resources/admin/js/components/multiselect.js';

const options = [
    { value: '1', parent: null, label: 'Board' },
    { value: '2', parent: '1', label: 'Tender' },
    { value: '3', parent: '2', label: 'Results' },
    { value: '4', parent: '1', label: 'Vacancy' },
    { value: '5', parent: null, label: 'Other' },
];

test('parent selection includes all depths even when search hides children', () => {
    const state = multiselect(options, [], true);
    state.search = 'Board';
    state.toggle('1');
    assert.deepEqual(state.selected, ['1', '2', '4', '3']);
    assert.equal(state.filteredOptions.length, 1);
    state.toggle('1');
    assert.deepEqual(state.selected, []);
});

test('removing a child clears selected ancestors but preserves other branches', () => {
    const state = multiselect(options, ['1', '5'], true);
    state.init();
    state.remove('2');
    assert.deepEqual(state.selected, ['4', '5']);
    state.toggle('1');
    assert.equal(new Set(state.selected).size, state.selected.length);
});

test('ordinary multiselects keep independent option selection', () => {
    const state = multiselect(options);
    state.toggle('1');
    assert.deepEqual(state.selected, ['1']);
    state.toggle('2');
    state.remove('1');
    assert.deepEqual(state.selected, ['2']);
});
