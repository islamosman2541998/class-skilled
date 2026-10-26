// Toasts (Notyf, §6.8): Livewire components dispatch `toast` {type, message};
// flashed session toasts are read from [data-cs-toast] elements.
import { Notyf } from 'notyf';

let notyf;

// Notyf adds its container to <body> once. wire:navigate swaps <body>, which leaves that container
// detached and every later toast invisible, so a new instance is made when it is gone.
function instance() {
    if (!notyf || !notyf.view?.container?.isConnected) {
        const mobile = window.matchMedia('(max-width: 639px)').matches;
        const rtl = document.documentElement.dir === 'rtl';
        notyf = new Notyf({
            duration: 4000,
            dismissible: true,
            ripple: false,
            position: { x: mobile ? 'center' : rtl ? 'left' : 'right', y: 'top' },
            types: [
                { type: 'success', background: '#157f3c' },
                { type: 'error', background: '#b42318' },
                { type: 'warning', background: '#b54708', icon: false },
                { type: 'info', background: '#2b4168', icon: false },
            ],
        });
    }
    return notyf;
}

export function toast(type, message) {
    if (!message) return;
    instance().open({ type: ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info', message });
}

export function registerToasts(Livewire) {
    window.csToast = toast;

    Livewire.on('toast', (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        toast(data?.type, data?.message);
    });

    const flash = () => {
        document.querySelectorAll('[data-cs-toast]').forEach((el) => {
            toast(el.dataset.type, el.dataset.message);
            el.remove();
        });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', flash);
    } else {
        flash();
    }
}
