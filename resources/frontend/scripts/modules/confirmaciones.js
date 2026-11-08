import Swal from 'sweetalert2';

let activa = null;

// La confirmación solo autoriza continuar la interacción; el servidor decide el resultado.
async function abrirDialogo({ title = 'Confirmar acción', text = '', subject = '', detail = '', confirmText = 'Confirmar', cancelText = 'Cancelar', tone = 'neutral', aviso = false, icon, input, inputLabel, inputPlaceholder, inputValue, inputValidator }, origen = null) {
    if (activa && document.querySelector('.rm-confirmation')) return null;
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
        const peligro = tone === 'danger' || icon === 'error';
        const tipo = ['success', 'error', 'warning', 'info', 'question'].includes(icon) ? icon : (peligro ? 'warning' : (aviso ? 'info' : 'question'));
        const icono = { success: 'ph-check-circle', error: 'ph-x-circle', warning: 'ph-warning-circle', info: 'ph-info', question: 'ph-shield-check' }[tipo];
        const modal = origen?.closest('.rm-modal-shell, .jetstream-modal, .rm-staff-overlay, [popover], dialog[open]');
        const resultado = await Swal.fire({
            target: modal?.querySelector('[data-rm-confirmation-host]') || modal || document.body,
            position: 'center',
            toast: false,
            heightAuto: false,
            backdrop: true,
            titleText: title,
            html: contenido,
            icon: tipo,
            iconHtml: `<i class="ph-bold ${icono}" aria-hidden="true"></i>`,
            showCancelButton: !aviso,
            showCloseButton: true,
            closeButtonAriaLabel: 'Cerrar aviso',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            focusConfirm: false,
            focusCancel: !aviso && !input,
            reverseButtons: true,
            buttonsStyling: false,
            allowOutsideClick: false,
            keydownListenerCapture: true,
            input, inputLabel, inputPlaceholder, inputValue, inputValidator,
            didOpen: () => {
                const container = Swal.getContainer();
                // La capa nativa mantiene el aviso por encima de ventanas y gráficas abiertas.
                if (container?.showPopover) {
                    container.setAttribute('popover', 'manual');
                    container.showPopover();
                }
            },
            customClass: {
                container: 'rm-confirmation-container',
                popup: `rm-confirmation${peligro ? ' rm-confirmation--danger' : (tipo !== 'question' ? ` rm-confirmation--${tipo}` : '')}`,
                icon: 'rm-confirmation__icon',
                confirmButton: `rm-btn ${peligro ? 'rm-btn-danger' : 'rm-btn-primary'}`,
                cancelButton: 'rm-btn rm-btn-secondary',
                closeButton: 'rm-confirmation__close',
                input: 'rm-input rm-confirmation__input',
                validationMessage: 'rm-error',
            },
        });
        return (!origen || origen.isConnected) ? resultado : null;
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

export async function confirmarOperacion(opciones, origen = null) {
    return Boolean((await abrirDialogo(opciones, origen))?.isConfirmed);
}

export async function mostrarAviso(opciones, origen = null) {
    return Boolean((await abrirDialogo({ confirmText: 'Entendido', ...opciones, aviso: true }, origen))?.isConfirmed);
}

export async function pedirTexto(opciones, origen = null) {
    const resultado = await abrirDialogo({ input: 'textarea', ...opciones }, origen);
    return resultado?.isConfirmed ? resultado.value : null;
}

let navegacionPermitida = null;
const salidaConfirmada = Symbol('salida-confirmada');
export function confirmarSalida(event, text, origen = null) {
    if (event[salidaConfirmada] || event.defaultPrevented) return;
    event.preventDefault();
    const url = new URL(event.detail.url, location.href);
    void confirmarOperacion({ title: 'Cambios sin guardar', text, confirmText: 'Salir sin guardar' }, origen).then(aceptado => {
        if (!aceptado) return;
        navegacionPermitida = url.href;
        // El siguiente evento de navegación atraviesa los mismos controles una vez.
        window.Livewire.navigate(url.href);
    });
}

// Se detiene el evento original antes de esperar. Solo una aceptación lo reanuda.
const permitidos = new WeakSet();
const enCurso = new WeakSet();
const huella = el => ['data-rm-confirm', 'wire:click', 'wire:submit', 'action', 'method', 'href'].map(a => el.getAttribute(a) || '').join('\n');
async function confirmarEvento(event, origen, destino, continuar) {
    if (permitidos.has(destino)) { permitidos.delete(destino); return; }
    const text = origen?.getAttribute('data-rm-confirm');
    if (!text || origen.disabled || event.defaultPrevented) return;
    event.preventDefault(); event.stopImmediatePropagation();
    if (enCurso.has(destino)) return;
    enCurso.add(destino);
    const original = huella(origen), originalDestino = huella(destino);
    try {
        const aceptado = await confirmarOperacion({
            title: origen.dataset.rmConfirmTitle || 'Confirmar acción', text: text.replaceAll('\\n', '\n'),
            subject: origen.dataset.rmConfirmSubject || '',
            confirmText: origen.dataset.rmConfirmLabel || 'Confirmar', tone: origen.dataset.rmConfirmTone || 'neutral',
        }, origen);
        if (!aceptado || !destino.isConnected || origen.disabled || huella(origen) !== original || huella(destino) !== originalDestino) return;
        permitidos.add(destino);
        try { continuar(); } finally { permitidos.delete(destino); }
    } finally { enCurso.delete(destino); }
}

if (typeof window !== 'undefined') {
    window.rmConfirmar = confirmarOperacion;
    window.rmAvisar = mostrarAviso;
    window.rmPedirTexto = pedirTexto;
    window.rmConfirmarSalida = confirmarSalida;
    document.addEventListener('livewire:navigate', event => {
        if (navegacionPermitida === new URL(event.detail.url, location.href).href) {
            event[salidaConfirmada] = true;
            navegacionPermitida = null;
        }
    }, true);
    document.addEventListener('click', event => {
        const el = event.target.closest?.('[data-rm-confirm]');
        if (!el || el instanceof HTMLFormElement) return;
        if (el.form && el.type === 'submit' && !el.hasAttribute('wire:click')) return;
        void confirmarEvento(event, el, el, () => el.click());
    }, true);
    document.addEventListener('submit', event => {
        const form = event.target;
        const origen = event.submitter?.hasAttribute('data-rm-confirm') ? event.submitter : form;
        void confirmarEvento(event, origen, form, () => form.requestSubmit(event.submitter || undefined));
    }, true);
}
