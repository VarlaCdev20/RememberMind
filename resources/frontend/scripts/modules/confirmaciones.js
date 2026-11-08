import Swal from 'sweetalert2';

let activa = null;

// La confirmación solo autoriza continuar la interacción; el servidor decide el resultado.
export async function confirmarOperacion({ title, text, subject = '', detail = '', confirmText = 'Confirmar', tone = 'neutral' }, origen = null) {
    if (activa && document.querySelector('.rm-confirmation')) return false;
    if (activa) Swal.close();
    const intento = Symbol('confirmacion');
    activa = intento;
    try {
        const contenido = document.createElement('div');
        if (subject) {
            const contexto = document.createElement('div');
            contexto.className = 'rm-confirmation__subject';
            const nombre = document.createElement('strong');
            nombre.textContent = subject;
            contexto.append(nombre);
            if (detail) {
                const dato = document.createElement('span');
                dato.textContent = detail;
                contexto.append(dato);
            }
            contenido.append(contexto);
        }
        const mensaje = document.createElement('p');
        mensaje.className = 'rm-confirmation__message';
        mensaje.textContent = text;
        contenido.append(mensaje);
        const peligro = tone === 'danger';
        const modal = origen?.closest('.rm-modal-shell, .jetstream-modal, .rm-staff-overlay');
        const resultado = await Swal.fire({
            target: modal?.querySelector('[data-rm-confirmation-host]') || modal || document.body,
            position: 'center',
            toast: false,
            heightAuto: false,
            backdrop: true,
            titleText: title,
            html: contenido,
            icon: peligro ? 'warning' : 'question',
            iconHtml: `<i class="ph-bold ${peligro ? 'ph-warning-circle' : 'ph-shield-check'}" aria-hidden="true"></i>`,
            showCancelButton: true,
            showCloseButton: true,
            closeButtonAriaLabel: 'Cerrar aviso',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            focusCancel: true,
            reverseButtons: true,
            buttonsStyling: false,
            allowOutsideClick: false,
            keydownListenerCapture: true,
            customClass: {
                container: 'rm-confirmation-container',
                popup: `rm-confirmation${peligro ? ' rm-confirmation--danger' : ''}`,
                icon: 'rm-confirmation__icon',
                confirmButton: `rm-btn ${peligro ? 'rm-btn-danger' : 'rm-btn-primary'}`,
                cancelButton: 'rm-btn rm-btn-secondary',
                closeButton: 'rm-confirmation__close',
            },
        });
        return resultado.isConfirmed && (!origen || origen.isConnected);
    } finally {
        if (activa === intento) {
            activa = null;
            if (origen?.isConnected) {
                const destino = origen.getClientRects().length ? origen : origen.closest('[x-data]')?.querySelector('button[aria-expanded]');
                destino?.focus({ preventScroll: true });
            }
        }
    }
}

if (typeof window !== 'undefined') window.rmConfirmar = confirmarOperacion;
