// Dashboard entry point. Livewire (with its bundled Alpine) is imported from the
// ESM build so our Alpine components register before Alpine starts.
import { Livewire, Alpine } from '../../../vendor/livewire/livewire/dist/livewire.esm';
import { registerToasts } from './toast';
import { registerConfirm } from './confirm';
import { registerSortable } from './sortable';
import { registerEditor } from './editor';
import { registerSelect } from './select';
import { registerDatepicker } from './datepicker';
import { registerUploader } from './uploader';
import { registerCharts } from './charts';

registerToasts(Alpine, Livewire);
registerConfirm(Alpine);
registerSortable(Alpine);
registerEditor(Alpine);
registerSelect(Alpine);
registerDatepicker(Alpine);
registerUploader(Alpine);
registerCharts(Alpine);

// Persisted UI state (sidebar groups, table columns). Storage may be unavailable.
const store = {
    get(key, fallback) {
        try {
            const raw = window.localStorage.getItem(key);
            return raw === null ? fallback : JSON.parse(raw);
        } catch {
            return fallback;
        }
    },
    set(key, value) {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch {
            // ignore
        }
    },
};
window.csStore = store;

Alpine.data('navGroup', (key, open = true) => ({
    open: store.get(`cs.nav.${key}`, open),
    toggle() {
        this.open = !this.open;
        store.set(`cs.nav.${key}`, this.open);
    },
}));

Alpine.data('columnToggle', (table, columns) => ({
    hidden: store.get(`cs.cols.${table}`, []),
    visible(column) {
        return !this.hidden.includes(column);
    },
    toggle(column) {
        this.hidden = this.visible(column) ? [...this.hidden, column] : this.hidden.filter((c) => c !== column);
        store.set(`cs.cols.${table}`, this.hidden);
    },
    columns,
}));

// Link object editor (see components/admin/link-picker.blade.php). Every change
// assigns a new object so x-modelable / wire:model pick it up.
Alpine.data('linkPicker', (options) => ({
    link: null,
    options,
    modelGroups() {
        const groups = { ...this.options.models };
        delete groups.page;
        return groups;
    },
    setType(type) {
        this.link = type ? { type, target_blank: false } : null;
    },
    set(key, value) {
        this.link = { ...(this.link ?? {}), [key]: value };
    },
    setModel(type, id) {
        this.link = { ...(this.link ?? {}), model_type: type, model_id: id ? Number(id) : null };
    },
}));

// Warn before leaving a form with unsaved changes (full reloads and wire:navigate).
Alpine.data('dirtyGuard', (message) => ({
    dirty: false,
    init() {
        this.$el.addEventListener('input', () => (this.dirty = true));
        this.$el.addEventListener('change', () => (this.dirty = true));
        this.$el.addEventListener('form-saved', () => (this.dirty = false));
        Livewire.on('form-saved', () => (this.dirty = false));

        this.beforeUnload = (event) => {
            if (this.dirty) {
                event.preventDefault();
                event.returnValue = message;
            }
        };
        window.addEventListener('beforeunload', this.beforeUnload);

        this.onNavigate = (event) => {
            if (!this.dirty) return;
            event.preventDefault();
            window.csConfirm({ title: message, confirmText: window.csLang?.leave ?? 'OK', danger: true }).then((ok) => {
                if (ok) {
                    this.dirty = false;
                    Livewire.navigate(event.detail.url.toString());
                }
            });
        };
        document.addEventListener('livewire:navigate', this.onNavigate);
    },
    destroy() {
        window.removeEventListener('beforeunload', this.beforeUnload);
        document.removeEventListener('livewire:navigate', this.onNavigate);
    },
}));

// Ctrl/Cmd + K opens the command palette.
document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('open-command-palette'));
    }
});

Livewire.start();
