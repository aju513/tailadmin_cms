<x-common.table-checkbox
    {{ $attributes }}
    x-bind:checked="visibleIds.length > 0 && selected.length === visibleIds.length"
    x-effect="$el.indeterminate = selected.length > 0 && selected.length < visibleIds.length"
    x-bind:disabled="visibleIds.length === 0"
    @change="selected = $event.target.checked ? [...visibleIds] : []"
/>
