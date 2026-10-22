// Feedback de transporte: nunca confirma persistencia ni reenvía una escritura.
const escrituras = new Set(['guardarAlerta', 'guardarRegistroEspecialidad', 'guardarAtencion', 'guardarAccion', 'confirmarCierre', 'asignarResponsable', 'reconocer', 'detectarAlertas']);
let registrado = false;

export function mensajeErrorAlerta(status) {
    if (status === 419) return 'La sesión caducó. Conserva tu texto antes de volver a iniciar sesión. El guardado no está confirmado.';
    if (status === 409) return 'La alerta cambió mientras trabajabas. Revisa su estado y su historial antes de volver a actuar.';
    return 'No se pudo confirmar la respuesta del servidor. Conserva tu captura y revisa el historial antes de volver a guardar.';
}

export function rmAlertasInteraccion() {
    return {
        solicitudes: 0, guardados: 0, errorSolicitud: '',
        estado({ inicio, escritura }) {
            this.solicitudes = Math.max(0, this.solicitudes + (inicio ? 1 : -1));
            if (escritura) this.guardados = Math.max(0, this.guardados + (inicio ? 1 : -1));
            if (inicio) this.errorSolicitud = '';
        },
        proteger(event) {
            if (this.guardados && (event.type === 'keydown' || event.target.closest('button, a'))) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        },
    };
}

export function registrarInteraccionAlertas() {
    if (registrado || !window.Livewire?.interceptRequest) return;
    registrado = true;
    window.Livewire.interceptRequest(({ request, onSend, onError, onFailure, onFinish }) => {
        const mensajes = Array.from(request.messages || []).filter(m => m.component?.el?.matches('.rm-pilot-alertas'));
        if (!mensajes.length) return;
        const destinos = [...new Set(mensajes.map(m => m.component.el))];
        const escritura = mensajes.some(m => Array.from(m.actions || []).some(a => escrituras.has(a.name)));
        const avisar = (tipo, detail) => destinos.forEach(el => el.dispatchEvent(new CustomEvent(tipo, { detail })));
        let enviado = false;
        onSend(() => { enviado = true; avisar('rm-alertas-request', { inicio: true, escritura }); });
        onError(({ response, preventDefault }) => {
            // El rechazo de autorización conserva el tratamiento institucional compartido.
            if (response.status === 403) return;
            if (![409, 419, 422, 429].includes(response.status) && response.status < 500) return;
            preventDefault();
            avisar('rm-alertas-error', { message: mensajeErrorAlerta(response.status) });
        });
        onFailure(() => avisar('rm-alertas-error', { message: mensajeErrorAlerta(0) }));
        onFinish(() => { if (enviado) avisar('rm-alertas-request', { inicio: false, escritura }); });
    });
}

if (typeof window !== 'undefined') {
    window.rmAlertasInteraccion = rmAlertasInteraccion;
    document.addEventListener('livewire:init', registrarInteraccionAlertas);
    registrarInteraccionAlertas();
}
