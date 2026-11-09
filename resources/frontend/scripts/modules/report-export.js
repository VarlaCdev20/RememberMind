import { crearVisorGraficas } from './report-charts.js';

export function crearExportacionReporte() {
    return {
        ...crearVisorGraficas(),
        exportacionAbierta: false,
        cargandoExportacion: false,
        descargandoExportacion: false,
        errorExportacion: '',
        resultadoExportacion: '',
        formatoExportacion: 'pdf',
        datosExportacion: null,
        partesExportacion: [],
        solicitudExportacion: null,
        origenExportacion: null,
        urlExportacion: '',

        init() {
            this.urlExportacion = this.$el.dataset.exportacionUrl;
            this.iniciarGraficas();
            this.$watch('partesExportacion', () => {
                clearTimeout(this.temporizadorGraficas);
                this.temporizadorGraficas = setTimeout(() => this.actualizarGraficasExportacion(), 150);
            });
        },

        actualizarGraficasExportacion() {
            if (!this.datosExportacion || !this.exportacionAbierta) return;
            if (!this.partesExportacion.length) { this.solicitudGraficas?.abort(); this.graficasReporte = []; this.cargandoGraficas = false; return; }
            const url = new URL(this.datosExportacion.graficas_url, window.location.origin);
            url.searchParams.set('solicitud', this.datosExportacion.solicitud);
            this.partesExportacion.forEach(parte => url.searchParams.append('partes[]', parte));
            this.consultarGraficas(url);
        },

        async abrirExportacion(origen = this.origenExportacion) {
            this.solicitudExportacion?.abort();
            const solicitud = new AbortController();
            this.solicitudExportacion = solicitud;
            this.origenExportacion = origen;
            this.urlExportacion = origen?.closest('[data-exportacion-url]')?.dataset.exportacionUrl || this.urlExportacion;
            this.exportacionAbierta = true;
            this.cargandoExportacion = true;
            this.errorExportacion = '';
            this.resultadoExportacion = '';
            this.datosExportacion = null;
            this.partesExportacion = [];
            try {
                const respuesta = await fetch(this.urlExportacion, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin', signal: solicitud.signal,
                });
                if (!respuesta.ok) throw new Error(this.mensajeErrorExportacion(respuesta.status));
                const datos = await respuesta.json();
                if (solicitud.signal.aborted) return;
                this.datosExportacion = datos;
                this.graficasReporte = datos.graficas;
                this.partesExportacion = datos.partes.map(parte => parte.id);
            } catch (error) {
                if (!solicitud.signal.aborted) this.errorExportacion = error instanceof SyntaxError || error instanceof TypeError
                    ? 'No se pudo consultar el reporte. Vuelve a intentarlo.' : error.message;
            } finally {
                if (this.solicitudExportacion === solicitud) this.cargandoExportacion = false;
            }
        },

        cerrarExportacion() {
            if (this.descargandoExportacion) return;
            this.solicitudExportacion?.abort();
            this.destruirGraficas();
            this.exportacionAbierta = false;
            this.$nextTick(() => this.origenExportacion?.isConnected && this.origenExportacion.focus());
        },

        seleccionarTodasExportacion(seleccionar) {
            this.partesExportacion = seleccionar ? this.datosExportacion.partes.map(parte => parte.id) : [];
        },

        async descargarExportacion() {
            if (this.descargandoExportacion || !this.datosExportacion || this.partesExportacion.length === 0) return;
            this.descargandoExportacion = true;
            this.errorExportacion = '';
            this.resultadoExportacion = '';
            try {
                const destino = new URL(this.datosExportacion[this.formatoExportacion], window.location.origin);
                destino.searchParams.set('solicitud', this.datosExportacion.solicitud);
                destino.searchParams.set('tipo_grafica', this.tipoGraficas);
                this.partesExportacion.forEach(parte => destino.searchParams.append('partes[]', parte));
                const respuesta = await fetch(destino, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                const tipo = this.formatoExportacion === 'pdf' ? 'application/pdf' : 'text/csv';
                if (!respuesta.ok) throw new Error(this.mensajeErrorExportacion(respuesta.status));
                if (!respuesta.headers.get('Content-Type')?.includes(tipo)) {
                    throw new Error('El archivo no pudo prepararse. Revisa tu sesión y vuelve a intentarlo.');
                }
                const archivo = await respuesta.blob();
                const enlace = document.createElement('a');
                const nombre = respuesta.headers.get('Content-Disposition')?.match(/filename="?([^";]+)"?/i)?.[1];
                enlace.href = URL.createObjectURL(archivo);
                enlace.download = nombre || 'reporte-gerencial.' + this.formatoExportacion;
                document.body.append(enlace);
                enlace.click();
                enlace.remove();
                setTimeout(() => URL.revokeObjectURL(enlace.href), 1000);
                this.resultadoExportacion = 'El archivo está preparado. La descarga se inició.';
            } catch (error) {
                this.errorExportacion = error instanceof TypeError
                    ? 'No se pudo conectar. Revisa tu conexión y vuelve a intentarlo.' : error.message;
            } finally {
                this.descargandoExportacion = false;
            }
        },

        mensajeErrorExportacion(estado) {
            if ([401, 403, 419].includes(estado)) return 'Tu sesión o tus permisos cambiaron. Revisa tu acceso antes de exportar.';
            if ([404, 409].includes(estado)) return 'La preparación ya no está disponible. Cierra esta ventana y abre de nuevo la exportación.';
            if (estado === 422) return 'Revisa la selección de contenido y vuelve a intentarlo.';
            return 'No se pudo preparar el reporte. Vuelve a intentarlo.';
        },

        destroy() { clearTimeout(this.temporizadorGraficas); this.destruirGraficas(); this.solicitudExportacion?.abort(); },
    };
}

if (typeof window !== 'undefined') window.rmReportExport = crearExportacionReporte;
