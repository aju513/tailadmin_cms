export function multiselect(options, selected = [], cascade = false) {
    return {
        open: false,
        search: '',
        options,
        selected: selected.map(String),
        get filteredOptions() {
            return this.options.filter(option => option.label.toLowerCase().includes(this.search.toLowerCase()));
        },
        branch(value) {
            const ids = [String(value)];
            if (cascade) {
                for (let index = 0; index < ids.length; index++) {
                    for (const option of this.options) {
                        if (option.parent === ids[index] && !ids.includes(option.value)) ids.push(option.value);
                    }
                }
            }
            return ids;
        },
        toggle(value) {
            value = String(value);
            if (this.selected.includes(value)) this.remove(value);
            else this.selected = [...new Set([...this.selected, ...this.branch(value)])];
        },
        remove(value) {
            const removed = this.branch(value);
            // Remove selected ancestors too, otherwise the server would re-add
            // the excluded branch when expanding a selected parent.
            if (cascade) {
                let parent = this.options.find(option => option.value === String(value))?.parent;
                while (parent && !removed.includes(parent)) {
                    removed.push(parent);
                    parent = this.options.find(option => option.value === parent)?.parent;
                }
            }
            this.selected = this.selected.filter(id => !removed.includes(id));
        },
        labelFor(value) {
            return this.options.find(option => option.value === String(value))?.label ?? value;
        },
        init() {
            this.selected = [...new Set(this.selected.flatMap(value => this.branch(value)))];
        },
    };
}
