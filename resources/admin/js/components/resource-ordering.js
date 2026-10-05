import { recordOrdering } from './record-ordering.js';

export function resourceOrdering(url, canReorder) {
    return recordOrdering(url, canReorder, { label: 'Resource', selectionKey: 'resources', rowAttribute: 'data-resource-id', rowKey: 'resourceId' });
}
