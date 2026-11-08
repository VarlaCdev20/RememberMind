import Swal from 'sweetalert2';

let abierta = false;

// La confirmación solo autoriza continuar la interacción; el servidor decide el resultado.
export async function confirmarOperacion({ title, text, subject = '', detail = '', confirmText = 'Confirmar', tone = 'neutral' }, origen = null) {
    if (abierta) return false;
    abierta = true;
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
        const resultado = await Swal.fire({
            target: origen?.closest('.rm-modal-shell, .jetstream-modal, .rm-staff-overlay') || document.body,
            titleText: title,
            html: contenido,
            icon: peligro ? 'warning' : 'question',
            iconHtml: `<i class="ph-bold ${peligro ? 'ph-warning-circle' : 'ph-shield-check'}" aria-hidden="true"></i>`,
            showCancelButton: true,
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
            },
        });
        return resultado.isConfirmed && (!origen || origen.isConnected);
    } finally {
        abierta = false;
        if (origen?.isConnected) {
            const destino = origen.getClientRects().length ? origen : origen.closest('[x-data]')?.querySelector('button[aria-expanded]');
            destino?.focus({ preventScroll: true });
        }
    }
}

if (typeof window !== 'undefined') window.rmConfirmar = confirmarOperacion;
