export function recordOrdering(url, canReorder, options = {}) {
    const label = options.label ?? 'Record';
    const rowAttribute = options.rowAttribute ?? 'data-record-id';
    const rowKey = options.rowKey ?? 'recordId';
    const selectionKey = options.selectionKey ?? 'records';

    return {
        canReorder, dragging: null, saving: false, message: '', failed: false, original: [],
        rows() { return [...this.$refs.rows.querySelectorAll(`[${rowAttribute}]`)]; },
        start(event) {
            if (!this.canReorder || this.saving || event.target.closest('a,button,input,select,textarea,label')) {
                event.preventDefault();
                return;
            }
            this.original = this.rows();
            this.dragging = event.currentTarget;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this.dragging.dataset[rowKey]);
            this.dragging.classList.add('opacity-50');
        },
        over(event) {
            if (this.dragging && !this.saving) {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
            }
        },
        drop(event) {
            event.preventDefault();
            const target = event.currentTarget;
            if (!this.dragging || this.dragging === target || this.saving) return;
            const rect = target.getBoundingClientRect();
            const after = event.clientY > rect.top + rect.height / 2;
            target.parentNode.insertBefore(this.dragging, after ? target.nextSibling : target);
            this.end();
            return this.persist();
        },
        end() {
            this.dragging?.classList.remove('opacity-50');
            this.dragging = null;
        },
        atEdge(row, direction) {
            const rows = this.rows();
            const position = rows.indexOf(row) + direction;
            return position < 0 || position >= rows.length;
        },
        move(row, direction) {
            if (!this.canReorder || this.saving || this.atEdge(row, direction)) return;
            this.original = this.rows();
            const adjacent = this.original[this.original.indexOf(row) + direction];
            if (direction < 0) this.$refs.rows.insertBefore(row, adjacent);
            else this.$refs.rows.insertBefore(adjacent, row);
            return this.persist();
        },
        async persist() {
            this.saving = true;
            this.failed = false;
            this.message = '';
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ [selectionKey]: this.rows().map(row => row.dataset[rowKey]), original_order: this.original.map(row => row.dataset[rowKey]) }),
                });
                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw new Error(Object.values(data.errors ?? {}).flat()[0] ?? `Could not save ${label.toLowerCase()} order. Reload and try again.`);
                }
                this.message = `${label} order updated.`;
            } catch (error) {
                this.original.forEach(row => this.$refs.rows.appendChild(row));
                this.failed = true;
                this.message = error.message;
            } finally {
                this.saving = false;
            }
        },
    };
}
