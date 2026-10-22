export const posesGuia = ['saludo', 'explicacion', 'atencion', 'duda', 'exito', 'aviso', 'empatia', 'descanso'];

export function rmAvatarGuia(estado = 'saludo', mensaje = '') {
    return {
        pose: posesGuia.includes(estado) ? estado : 'saludo', texto: mensaje, pausada: false,
        iconos: { saludo: 'ph-hand-waving', explicacion: 'ph-book-open', atencion: 'ph-eye', duda: 'ph-question', exito: 'ph-check-circle', aviso: 'ph-warning-circle', empatia: 'ph-heart', descanso: 'ph-moon' },
        recibir(pose, texto) {
            if (!posesGuia.includes(pose) || this.pausada) return;
            this.pose = pose; this.texto = texto;
        },
        pausar() {
            this.pausada = !this.pausada;
            this.pose = this.pausada ? 'descanso' : 'saludo';
            this.texto = this.pausada ? 'La ayuda está en pausa. Los avisos y errores del formulario siguen visibles.' : mensaje;
        },
    };
}

export function rmGuiaVisual() {
    return {
        tab: 'resumen', paso: 0,
        seleccionar(tab) { this.tab = tab; this.paso = 0; },
        mover(delta, total) { this.paso = Math.max(0, Math.min(total - 1, this.paso + delta)); },
        teclado(event) {
            const botones = [...event.currentTarget.querySelectorAll('[role="tab"]')];
            const indice = botones.indexOf(document.activeElement);
            if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const destino = event.key === 'Home' ? 0 : event.key === 'End' ? botones.length - 1 : (indice + (event.key === 'ArrowRight' ? 1 : -1) + botones.length) % botones.length;
            botones[destino].click(); botones[destino].focus();
        },
    };
}

export function mensajeErrorValoracion(status) {
    if (status === 419) return 'La sesión caducó. Conserva tus respuestas antes de iniciar sesión. El guardado no está confirmado.';
    if (status === 409) return 'El catálogo o la fuente cambió. Conserva la captura y revisa la versión vigente antes de continuar.';
    return 'No se pudo confirmar la respuesta del servidor. Conserva tus respuestas y consulta el historial antes de volver a guardar.';
}

let feedbackRegistrado = false;
export function registrarFeedbackValoraciones() {
    if (feedbackRegistrado || !window.Livewire?.interceptRequest) return;
    feedbackRegistrado = true;
    window.Livewire.interceptRequest(({ request, onSend, onError, onFailure }) => {
        const destinos = Array.from(request.messages || []).map(m => m.component?.el)
            .filter(el => el?.matches('.rm-valoraciones-area, .rm-captura-instrumental'));
        if (!destinos.length) return;
        onSend(() => destinos.forEach(el => el.dispatchEvent(new CustomEvent('rm-valoraciones-inicio'))));
        const avisar = status => destinos.forEach(el => el.dispatchEvent(new CustomEvent('rm-valoraciones-error', { detail: { message: mensajeErrorValoracion(status) } })));
        onError(({ response, preventDefault }) => {
            if (response.status === 403) return;
            if (![409, 419, 422, 429].includes(response.status) && response.status < 500) return;
            preventDefault(); avisar(response.status);
        });
        onFailure(() => avisar(0));
    });
}

if (typeof window !== 'undefined') {
    window.rmAvatarGuia = rmAvatarGuia;
    window.rmGuiaVisual = rmGuiaVisual;
    document.addEventListener('livewire:init', registrarFeedbackValoraciones);
    registrarFeedbackValoraciones();
}
