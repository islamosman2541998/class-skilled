// Confirmations (SweetAlert2, §6.8): `$confirm({title, text, confirmText, cancelText, danger})`
// returns a Promise<boolean>. SweetAlert2 is downloaded on the first call only.
export async function confirmDialog({ title, text = '', confirmText, cancelText, danger = false } = {}) {
    const [{ default: Swal }] = await Promise.all([
        import('sweetalert2/dist/sweetalert2.js'),
        import('sweetalert2/dist/sweetalert2.css'),
    ]);
    const lang = window.csLang ?? {};
    const result = await Swal.fire({
        title,
        text,
        icon: danger ? 'warning' : 'question',
        iconColor: danger ? '#f7a85b' : '#4fc3ee',
        showCancelButton: true,
        confirmButtonText: confirmText ?? lang.confirm ?? 'OK',
        cancelButtonText: cancelText ?? lang.cancel ?? 'Cancel',
        confirmButtonColor: danger ? '#b42318' : '#2b4168',
        focusCancel: danger,
    });
    return result.isConfirmed;
}

export function registerConfirm(Alpine) {
    window.csConfirm = confirmDialog;
    Alpine.magic('confirm', () => (options) => confirmDialog(options));
}
