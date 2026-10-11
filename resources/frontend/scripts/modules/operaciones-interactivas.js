import { rmResidentes } from './residentes-interactivos.js';

export function rmOperaciones(modulo = 'general') {
    return {
        ...rmResidentes(),
        graficos: true,
        modulo,
        init() {
            const inicial = !window.matchMedia('(max-width: 640px)').matches;
            try {
                const guardada = window.localStorage?.getItem(`rm.operaciones.graficos.${this.modulo}`);
                this.graficos = guardada === 'true' ? true : (guardada === 'false' ? false : inicial);
            } catch { this.graficos = inicial; }
            this.finalizarNavegacion();
        },
        alternarGraficos() {
            this.graficos = !this.graficos;
            try { window.localStorage?.setItem(`rm.operaciones.graficos.${this.modulo}`, String(this.graficos)); }
            catch { this.preferenciaNoDisponible = true; }
        },
        ayuda: false,
        fichaHtml: '',
        fichaAbierta: false,
        cargandoFicha: false,
        errorFicha: '',
        fichaSolicitud: 0,
        triggerFicha: null,
        async abrirFicha(url, codigo, evento) {
            const destino = new URL(url, window.location.origin);
            if (destino.origin !== window.location.origin || destino.pathname !== window.location.pathname) {
                this.errorFicha = 'La ficha debe pertenecer a esta ventana.';
                return;
            }
            this.triggerFicha = evento?.currentTarget || document.activeElement;
            this.solicitudFicha?.abort();
            this.solicitudFicha = new AbortController();
            const solicitud = ++this.fichaSolicitud;
            this.cargandoFicha = true;
            this.errorFicha = '';
            try {
                const respuesta = await fetch(destino.href, { credentials: 'same-origin', headers: { 'X-RM-Ficha': '1', Accept: 'text/html' }, signal: this.solicitudFicha.signal });
                if (!respuesta.ok || respuesta.redirected || respuesta.headers.get('X-RM-Ficha') !== '1') {
                    throw new Error('Ficha no disponible');
                }
                const contenido = await respuesta.text();
                if (solicitud !== this.fichaSolicitud) return;
                this.fichaHtml = contenido;
                this.fichaAbierta = true;
            } catch (error) {
                if (error.name !== 'AbortError' && solicitud === this.fichaSolicitud) this.errorFicha = 'No se pudo abrir la ficha. Revisa tu sesión o vuelve a intentarlo.';
            } finally {
                if (solicitud === this.fichaSolicitud) this.cargandoFicha = false;
            }
        },
        cerrarFicha() {
            this.solicitudFicha?.abort();
            this.fichaSolicitud++;
            this.fichaAbierta = false;
            this.cargandoFicha = false;
            if (window.location.href) {
                const actual = new URL(window.location.href);
                if (actual.searchParams.has('detalle')) {
                    actual.searchParams.delete('detalle');
                    window.history.replaceState(window.history.state, '', actual.href);
                }
            }
            const referencia = this.triggerFicha;
            this.$nextTick(() => {
                this.fichaHtml = '';
                referencia?.focus({ preventScroll: true });
            });
        },
        abrirAyuda(evento) {
            this.triggerAyuda = evento.currentTarget;
            this.ayuda = true;
            this.$nextTick(() => this.$refs.cerrarAyuda.focus());
        },
        cerrarAyuda() {
            this.ayuda = false;
            this.$nextTick(() => this.triggerAyuda?.focus({ preventScroll: true }));
        },
        indicador: null,
        abrirIndicador(datos, evento) {
            this.triggerIndicador = evento?.currentTarget || document.activeElement;
            this.indicador = datos;
            this.$nextTick(() => this.$refs.cerrarIndicador.focus());
        },
        cerrarIndicador() {
            this.indicador = null;
            this.$nextTick(() => this.triggerIndicador?.focus({ preventScroll: true }));
        },
        finalizarNavegacion() { this.loading = false; },
    };
}
if (typeof window !== 'undefined') window.rmOperaciones = rmOperaciones;
