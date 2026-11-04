// El estado lo decide el backend. Un fallo de red nunca acredita fallo o entrega del correo.
export async function solicitarCorreo(url, fetcher = globalThis.fetch, csrf = '') {
    const response = await fetcher(url, {
        method: 'POST', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
        const error = new Error(data.message || 'No se pudo registrar la solicitud.');
        error.rechazoConfirmado = response.status >= 400 && response.status < 500;
        throw error;
    }
    return data;
}

export async function consultarEstadoCorreo(url, fetcher = globalThis.fetch) {
    const response = await fetcher(url, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error('No se pudo consultar el estado.');
    return response.json();
}

if (typeof window !== 'undefined') {
    const dialogo = () => window.SwalAmandita || window.Swal;
    const activos = new Set();
    window.RMCorreo = {
        async enviar(url) {
            if (activos.has(url)) return;
            activos.add(url);
            try {
                dialogo().fire({ title: 'Registrando solicitud', text: 'Espere la confirmación del servidor.', allowOutsideClick: false, didOpen: () => window.Swal.showLoading() });
                let data;
                try {
                    data = await solicitarCorreo(url, window.fetch.bind(window), document.querySelector('meta[name="csrf-token"]')?.content || '');
                    if (data.duplicado) data.message = 'Esta solicitud ya estaba registrada. Mostramos el estado de ese envío. ' + data.message;
                } catch (error) {
                    await dialogo().fire(error.rechazoConfirmado
                        ? { icon: 'error', title: 'Solicitud rechazada', text: error.message }
                        : { icon: 'warning', title: 'Solicitud sin confirmar', text: 'No se pudo confirmar la solicitud. Puede consultar de nuevo; una solicitud pendiente se recupera sin crear otro envío.' });
                    return;
                }
                if (new URL(data.estado_url, location.origin).origin !== location.origin) throw new Error('URL de estado inválida.');
                await this.seguir(data);
            } finally {
                activos.delete(url);
            }
        },
        async seguir(initial) {
            let data = initial;
            let abierta = true;
            let timer;
            const icono = estado => estado === 'ACEPTADO' ? 'success' : estado === 'FALLIDO' ? 'error' : 'info';
            const result = await dialogo().fire({
                icon: icono(data.estado), title: data.titulo, text: data.message,
                confirmButtonText: data.terminado ? 'Cerrar' : 'Actualizar estado', showCancelButton: !data.terminado, cancelButtonText: 'Cerrar',
                didOpen: () => {
                    let consultas = 0;
                    const consultar = async () => {
                        if (!abierta || data.terminado || consultas++ >= 15) return;
                        try {
                            const siguiente = await consultarEstadoCorreo(initial.estado_url, window.fetch.bind(window));
                            if (!abierta) return;
                            data = { ...siguiente, estado_url: initial.estado_url };
                            window.Swal.update({ icon: icono(data.estado), title: data.titulo, text: data.message, confirmButtonText: data.terminado ? 'Cerrar' : 'Actualizar estado', showCancelButton: !data.terminado });
                        } catch {
                            if (abierta) window.Swal.update({ icon: 'warning', title: 'Estado sin actualizar', text: 'No se pudo consultar el estado. La solicitud puede seguir en proceso. Pulse Actualizar estado para comprobarla.' });
                            return;
                        }
                        if (abierta && !data.terminado) timer = window.setTimeout(consultar, 2000);
                    };
                    if (!data.terminado) timer = window.setTimeout(consultar, 1000);
                },
                willClose: () => { abierta = false; window.clearTimeout(timer); },
            });
            if (result.isConfirmed && !data.terminado) {
                try { data = { ...await consultarEstadoCorreo(initial.estado_url, window.fetch.bind(window)), estado_url: initial.estado_url }; }
                catch {
                    data = { ...data, titulo: 'Estado sin actualizar', message: 'No se pudo consultar el estado. Se conserva el último estado conocido; vuelva a comprobarlo cuando tenga conexión.' };
                }
                return this.seguir(data);
            }
        },
    };
}
