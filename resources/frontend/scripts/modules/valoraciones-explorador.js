export const posesGuia = ['saludo', 'explicacion', 'atencion', 'duda', 'exito', 'aviso', 'empatia', 'descanso'];

export function rmAvatarGuia(estado = 'saludo', mensaje = '') {
    return {
        pose: posesGuia.includes(estado) ? estado : 'saludo', texto: mensaje, pausada: false, imagenAusente: false,
        ultimoContexto: { pose: posesGuia.includes(estado) ? estado : 'saludo', texto: mensaje },
        iconos: { saludo: 'ph-hand-waving', explicacion: 'ph-book-open', atencion: 'ph-eye', duda: 'ph-question', exito: 'ph-check-circle', aviso: 'ph-warning-circle', empatia: 'ph-heart', descanso: 'ph-moon' },
        recibir(pose, texto) {
            if (!posesGuia.includes(pose)) return;
            this.ultimoContexto = { pose, texto };
            if (this.pausada) return;
            this.pose = pose; this.texto = texto;
        },
        pausar() {
            this.pausada = !this.pausada;
            this.pose = this.pausada ? 'descanso' : this.ultimoContexto.pose;
            this.texto = this.pausada ? 'La ayuda está en pausa. Los avisos y errores del formulario siguen visibles.' : this.ultimoContexto.texto;
        },
    };
}

export function rmGuiaVisual(campos = []) {
    const secciones = ['resumen', 'pasos', 'materiales', 'condiciones', 'datos', 'aporte', 'fuentes'];
    const mensajes = {
        resumen: ['explicacion', 'Vamos a conocer esta ficha. Puedes recorrer su preparación y explorar qué dato se conserva en cada campo.'],
        pasos: ['explicacion', 'Avanza a tu ritmo. Cada paso destaca el dato que debes revisar; aquí no se guarda ninguna respuesta clínica.'],
        materiales: ['atencion', 'Comprueba la edición y sus materiales autorizados antes de cualquier aplicación.'],
        condiciones: ['atencion', 'La revisión de esta ayuda no acredita condiciones clínicas ni autoriza una aplicación.'],
        datos: ['duda', 'Selecciona un campo para conocer su propósito y su procedencia. Los valores reales se introducen en la captura autorizada.'],
        aporte: ['empatia', 'Cuando falta evidencia, explicar lo que aún no sabemos también ayuda a cuidar.'],
        fuentes: ['atencion', 'Consulta el origen y la versión. La documentación de investigación no sustituye el manual clínico.'],
    };
    return {
        tab: 'resumen', paso: 0, campo: 0, revisados: [], campos,
        avisar(pose, texto) { this.$dispatch?.('rm-guia-contexto', { pose, texto }); },
        seleccionar(tab) {
            if (!secciones.includes(tab)) return;
            this.tab = tab; this.paso = 0;
            this.avisar(...mensajes[tab]);
        },
        irPaso(indice, total) {
            this.paso = Math.max(0, Math.min(total - 1, indice));
            this.campo = Math.min(this.paso, Math.max(0, this.campos.length - 1));
            this.avisar(this.paso === 3 ? 'atencion' : 'explicacion', this.campos[this.campo]?.ayuda || mensajes.pasos[1]);
        },
        mover(delta, total) { this.irPaso(this.paso + delta, total); },
        elegirCampo(indice) {
            if (!Number.isInteger(indice) || !this.campos[indice]) return;
            this.campo = indice;
            this.avisar('duda', this.campos[indice].ayuda);
        },
        revisarPaso() {
            this.revisados = this.revisados.includes(this.paso) ? this.revisados.filter(p => p !== this.paso) : [...this.revisados, this.paso];
            this.avisar('explicacion', this.revisados.includes(this.paso) ? 'Paso marcado como revisado en esta visita. Esto no confirma una aplicación ni valida datos clínicos.' : 'Puedes volver a revisar este paso cuando lo necesites.');
        },
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
