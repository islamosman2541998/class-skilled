// Library asset field (Livewire AssetField). A pick, a removal or a new order updates `$wire.value`
// in the browser at once, so a Save pressed right after (before the field's own request returns on a
// slow host) already carries the new value. The server methods still run to redraw the thumbnails;
// they give the same result, so running both is safe.
export function registerAssetField(Alpine) {
    Alpine.data('assetField', ({ id, multiple = false }) => ({
        current() {
            const value = this.$wire.value;
            return (Array.isArray(value) ? value : [value]).map(Number).filter(Boolean);
        },
        picked(detail) {
            if (detail?.target !== id) return;
            const ids = (detail.ids || []).map(Number);
            this.$wire.value = multiple ? [...new Set([...this.current(), ...ids])] : (ids[0] ?? null);
        },
        remove(assetId) {
            this.$wire.value = multiple ? this.current().filter((item) => item !== assetId) : null;
            this.$wire.remove(assetId);
        },
        reorder(ids) {
            const order = ids.map(Number);
            this.$wire.value = order.filter((item) => this.current().includes(item));
            this.$wire.reorder(order);
        },
    }));
}
