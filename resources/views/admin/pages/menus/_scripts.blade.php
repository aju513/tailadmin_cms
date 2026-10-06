<script>
window.menuManager = (orderUrl, menuId, initialPanel = 'pages') => ({
    activePanel: initialPanel,
    showSubmenus: true,
    selected: [],
    dragging: null,
    message: '',
    failed: false,
    get itemIds() {
        return [...this.$refs.rows.querySelectorAll('[data-menu-item-id]')].map(row => row.dataset.menuItemId);
    },
    get allSelected() {
        return this.itemIds.length > 0 && this.selected.length === this.itemIds.length;
    },
    toggleAll(checked) {
        this.selected = checked ? this.itemIds : [];
    },
    renumber() {
        [...this.$refs.rows.querySelectorAll('[data-serial]')].forEach((cell, index) => { cell.textContent = `${index + 1}.`; });
    },
    start(event) {
        const row = event.currentTarget;
        if (row.dataset.dragEnabled !== 'true') {
            event.preventDefault();
            return;
        }
        this.dragging = row;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', row.dataset.menuItemId);
        row.classList.add('opacity-50');
    },
    over(event) {
        if (this.dragging && this.dragging.dataset.parentId === event.currentTarget.dataset.parentId) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        }
    },
    drop(event) {
        event.preventDefault();
        const target = event.currentTarget;
        if (!this.dragging || this.dragging === target || this.dragging.dataset.parentId !== target.dataset.parentId) return;

        const movingRows = this.blockFor(this.dragging);
        const targetRows = this.blockFor(target);
        const after = event.clientY > target.getBoundingClientRect().top + target.getBoundingClientRect().height / 2;
        const anchor = after ? targetRows[targetRows.length - 1].nextElementSibling : targetRows[0];
        if (anchor && movingRows.includes(anchor)) return;

        movingRows.forEach(row => row.remove());
        movingRows.forEach(row => this.$refs.rows.insertBefore(row, anchor));
        this.renumber();
        this.persist(target.dataset.parentId);
    },
    end(event) {
        event.currentTarget.classList.remove('opacity-50');
        event.currentTarget.draggable = false;
        delete event.currentTarget.dataset.dragEnabled;
        this.dragging = null;
    },
    blockFor(row) {
        const rows = [...this.$refs.rows.querySelectorAll('[data-menu-item-id]')];
        const start = rows.indexOf(row);
        const depth = Number(row.dataset.depth);
        const block = [row];
        for (let index = start + 1; index < rows.length && Number(rows[index].dataset.depth) > depth; index++) block.push(rows[index]);
        return block;
    },
    persist(parentId) {
        const itemIds = [...this.$refs.rows.querySelectorAll('[data-menu-item-id]')]
            .filter(row => row.dataset.parentId === parentId)
            .map(row => row.dataset.menuItemId);

        fetch(orderUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ menu_id: menuId, parent_id: parentId || null, menu_items: itemIds }),
        })
            .then(response => { if (!response.ok) throw new Error(); return response.json(); })
            .then(data => {
                this.failed = false;
                this.message = data.message;
                setTimeout(() => this.message = '', 2500);
            })
            .catch(() => {
                this.failed = true;
                this.message = 'Unable to update the menu order. Reloading the saved order.';
                setTimeout(() => window.location.reload(), 1200);
            });
    },
    init() {
        this.$nextTick(() => this.renumber());
    },
});
</script>
