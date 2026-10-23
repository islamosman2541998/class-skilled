// Confirmations (SweetAlert2): `$confirm({title, text, confirmText, cancelText, danger})`
// returns a Promise<boolean>. Usage: @click="$confirm({...}).then(ok => ok && $wire.delete(id))"
import Swal from 'sweetalert2/dist/sweetalert2.js';
import 'sweetalert2/dist/sweetalert2.css';

export function confirmDialog({ title, text = '', confirmText, cancelText, danger = false } = {}) {
    const lang = window.csLang ?? {};
    return Swal.fire({
        title,
        text,
        icon: danger ? 'warning' : 'question',
        iconColor: danger ? '#f7a85b' : '#4fc3ee',
        showCancelButton: true,
        confirmButtonText: confirmText ?? lang.confirm ?? 'OK',
        cancelButtonText: cancelText ?? lang.cancel ?? 'Cancel',
        confirmButtonColor: danger ? '#b42318' : '#2b4168',
        reverseButtons: false,
        focusCancel: danger,
        customClass: { popup: document.documentElement.dir === 'rtl' ? 'swal-rtl' : '' },
    }).then((result) => result.isConfirmed);
}

export function registerConfirm(Alpine) {
    window.csConfirm = confirmDialog;
    Alpine.magic('confirm', () => (options) => confirmDialog(options));
}
