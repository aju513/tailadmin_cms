export function capacityReportEditor(initial) {
    let sequence = 0;
    const row = (item = {}) => ({ uid: ++sequence, key: item.key ?? '', value: item.value ?? '', keyError: item.keyError ?? '', valueError: item.valueError ?? '' });

    return {
        development: initial.development.map(row),
        collaboration: initial.collaboration.map(row),
        maxRows: initial.maxRows,
        add(group) {
            if (this[group].length >= this.maxRows) return;
            const item = row();
            this[group].push(item);
            this.$nextTick(() => document.getElementById(`metric-${group}-${item.uid}-key`)?.focus());
        },
        remove(group, index) {
            if (this[group].length > 1) this[group].splice(index, 1);
        },
        move(group, index, offset) {
            const destination = index + offset;
            if (destination < 0 || destination >= this[group].length) return;
            const [item] = this[group].splice(index, 1);
            this[group].splice(destination, 0, item);
        },
    };
}
