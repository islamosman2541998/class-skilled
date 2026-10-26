// Searchable select (Tom Select) bound with x-modelable:
// <div x-data="tomSelect({ multiple: true, sortable: true })" x-modelable="value" wire:model="form.ids" wire:ignore>
//   <select x-ref="select">…</select>
// </div>
import TomSelect from 'tom-select/base';
import RemoveButton from 'tom-select/dist/esm/plugins/remove_button/plugin.js';
import DragDrop from 'tom-select/dist/esm/plugins/drag_drop/plugin.js';

TomSelect.define('remove_button', RemoveButton);
TomSelect.define('drag_drop', DragDrop);

export function registerSelect(Alpine) {
    Alpine.data('tomSelect', ({ multiple = false, placeholder = '', allowEmpty = true, create = false, noResults = '' } = {}) => {
        let ts = null;

        return {
            value: multiple ? [] : '',
            init() {
                const plugins = multiple ? ['remove_button'] : [];
                ts = new TomSelect(this.$refs.select, {
                    plugins,
                    create,
                    placeholder,
                    allowEmptyOption: allowEmpty,
                    maxOptions: 500,
                    hidePlaceholder: false,
                    render: { no_results: () => `<div class="no-results">${noResults}</div>` },
                    // Tom Select hands over its own items array and changes it in place when an item is removed,
                    // so it is copied: assigning the same array again would not notify Livewire.
                    onChange: (value) => {
                        this.value = multiple ? (Array.isArray(value) ? [...value] : String(value).split(',').filter(Boolean)) : value;
                    },
                });
                this.sync(this.value);
                this.$watch('value', (value) => this.sync(value));
            },
            sync(value) {
                if (!ts) return;
                const normalized = multiple ? (value || []).map(String) : String(value ?? '');
                const current = ts.getValue();
                if (JSON.stringify(current) !== JSON.stringify(normalized)) ts.setValue(normalized, true);
            },
            destroy() {
                ts?.destroy();
                ts = null;
            },
        };
    });
}
