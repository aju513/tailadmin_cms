export function pageManager(statuses, selectionKey = 'pages') {
    return {
        selected: [],
        visibleIds: Object.keys(statuses),
        statuses,
        statusBusy: false,
        pendingIds: [],
        statusMessage: '',
        statusError: false,
        async changeStatus(url, method, ids, status = null) {
            if (this.statusBusy || ids.length === 0) return;
            this.statusBusy = true;
            this.pendingIds = ids;
            this.statusMessage = '';
            this.statusError = false;
            try {
                const response = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(status === null ? {} : { [selectionKey]: ids, status }),
                });
                const data = await response.json().catch(() => null);
                const records = data?.records ?? data?.pages;
                if (!response.ok || response.redirected || !Array.isArray(records)) {
                    const message = response.status === 419 || response.status === 401 || response.redirected
                        ? 'Your session expired. Refresh the page and sign in again.'
                        : response.status === 403
                            ? 'You do not have permission to change this status.'
                            : Object.values(data?.errors ?? {}).flat()[0] ?? data?.message ?? 'Unable to update status. Please try again.';
                    throw new Error(message);
                }
                for (const record of records) this.statuses[String(record.id)] = String(record.status);
                this.statusMessage = data.message;
            } catch (error) {
                this.statusError = true;
                this.statusMessage = error.message || 'Unable to update status. Please try again.';
            } finally {
                this.statusBusy = false;
                this.pendingIds = [];
            }
        },
    };
}
