// Toasts (Notyf): Livewire components dispatch `toast` {type, message};
// flashed session toasts are read from [data-cs-toast] elements.
import { Notyf } from 'notyf';

let notyf;

function instance() {
    if (!notyf) {
        const mobile = window.matchMedia('(max-width: 639px)').matches;
        const rtl = document.documentElement.dir === 'rtl';
        notyf = new Notyf({
            duration: 3500,
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

export function registerToasts(Alpine, Livewire) {
    window.csToast = toast;

    Livewire.on('toast', (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        toast(data?.type, data?.message);
    });

    // A request that fails on the server or never arrives is never silent. 419 (expired page) keeps
    // Livewire's own prompt; with APP_DEBUG the error page modal stays for the developer.
    Livewire.interceptRequest(({ onError, onFailure }) => {
        onError(({ response, preventDefault }) => {
            if (response.status === 419) return;
            if (!window.csDebug) preventDefault();
            toast('error', window.csLang?.server_error);
        });
        onFailure(() => toast('error', window.csLang?.connection_error));
    });

    const flash = () => {
        document.querySelectorAll('[data-cs-toast]').forEach((el) => {
            toast(el.dataset.type, el.dataset.message);
            el.remove();
        });
    };
    document.addEventListener('DOMContentLoaded', flash);
    document.addEventListener('livewire:navigated', flash);
}
