// Un rechazo conserva la pantalla y su captura; nunca se reintenta una operación clínica.
let registrado = false;
let mostrandoDialogo = false;
let rechazoMostrado = false;
const lecturasAutomaticas = new Set(['$refresh', 'refrescarTurno', 'verificarAlertas']);

export function registrarRespuestaAcceso() {
    if (registrado || !window.Livewire?.interceptRequest) return;
    registrado = true;
    window.Livewire.interceptRequest(({ request, onError }) => {
        onError(({ response, preventDefault }) => {
            if (response.status !== 403) return;
            const aviso = document.querySelector('[data-rm-access-rejection]');
            if (!aviso) return;

            preventDefault();
            aviso.hidden = false;
            const acciones = Array.from(request?.messages || []).flatMap(m => Array.from(m.actions || []));
            const automatico = acciones.every(a => lecturasAutomaticas.has(a.name));
            if (mostrandoDialogo || (automatico && rechazoMostrado)) return;
            rechazoMostrado = true;

            const mensaje = 'El servidor rechazó esta solicitud. Si estabas guardando, el resultado no está confirmado. Al volver al inicio perderás los cambios sin guardar.';
            const dialogo = window.SwalAmandita || window.Swal;
            if (!dialogo?.fire) {
                window.alert(mensaje);
                return;
            }
            mostrandoDialogo = true;
            Promise.resolve(dialogo.fire({
                icon: 'warning', title: 'Solicitud rechazada', text: mensaje,
                showCancelButton: true, confirmButtonText: 'Volver a mi inicio',
                cancelButtonText: 'Mantener pantalla', allowOutsideClick: false,
            })).then(resultado => {
                const inicio = aviso.querySelector('[data-rm-access-home]');
                if (resultado.isConfirmed && inicio) window.location.assign(inicio.href);
            }).finally(() => { mostrandoDialogo = false; });
        });
    });
}

document.addEventListener('livewire:init', registrarRespuestaAcceso);
registrarRespuestaAcceso();
