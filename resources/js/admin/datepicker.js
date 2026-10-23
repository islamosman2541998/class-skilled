// Date / datetime picker (flatpickr, Arabic locale) bound with x-modelable:
// <div x-data="datePicker({ time: true })" x-modelable="value" wire:model="form.published_at" wire:ignore>
//   <input x-ref="input" class="a-input">
// </div>
// The model value is ISO "Y-m-d" or "Y-m-d H:i".
import flatpickr from 'flatpickr';
import { Arabic } from 'flatpickr/dist/l10n/ar.js';

export function registerDatepicker(Alpine) {
    Alpine.data('datePicker', ({ time = false } = {}) => {
        let fp = null;

        return {
            value: '',
            init() {
                const ar = document.documentElement.lang === 'ar';
                fp = flatpickr(this.$refs.input, {
                    locale: ar ? { ...Arabic, firstDayOfWeek: 6 } : 'default',
                    enableTime: time,
                    time_24hr: true,
                    dateFormat: time ? 'Y-m-d H:i' : 'Y-m-d',
                    altInput: true,
                    altFormat: time ? 'd/m/Y H:i' : 'd/m/Y',
                    allowInput: true,
                    defaultDate: this.value || null,
                    onChange: (dates, str) => {
                        this.value = str;
                    },
                });
                this.$watch('value', (value) => {
                    if (fp && (value || '') !== fp.input.value) fp.setDate(value || null, false);
                });
            },
            clear() {
                fp?.clear();
                this.value = '';
            },
            destroy() {
                fp?.destroy();
                fp = null;
            },
        };
    });
}
