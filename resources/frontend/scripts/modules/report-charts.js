export function tipoDeGrafica(grafica, preferido = 'auto') {
    return grafica.tipos.includes(preferido) ? preferido : preferido === 'auto' ? grafica.recomendado : 'barras';
}

const tonosGrafica = ['care', 'clinical', 'cognitive', 'rehab', 'reference'];

export function configurarGraficaReporte(api, grafica, tipo) {
    const colores = (grafica.tonos ?? tonosGrafica).map(tono => api.color(tono));
    const etiquetas = grafica.serie.map(punto => punto.etiqueta);
    const valores = grafica.serie.map(punto => punto.valor);
    const comunes = { plugins: { legend: { display: false }, datalabels: { display: false } } };
    if (tipo === 'dona' || tipo === 'torta') {
        return api.presets[tipo === 'dona' ? 'doughnut' : 'pie'](etiquetas, valores, colores.slice(0, valores.length), comunes);
    }
    if (tipo === 'radar') {
        const config = api.presets.radar(etiquetas.map((_, i) => String(i + 1)), [{ label: grafica.unidad, data: valores,
            backgroundColor: api.hexToRgba(colores[0], .28), borderColor: colores[0], pointBackgroundColor: colores[0] }], comunes);
        config.options.scales.r.min = 0;
        config.options.scales.r.max = grafica.maximo || 1;
        config.options.scales.r.ticks.display = true;
        config.options.scales.r.ticks.precision = 0;
        config.options.scales.r.ticks.stepSize = Math.max(1, Math.ceil((grafica.maximo || 1) / 4));
        config.options.plugins.tooltip = { callbacks: { title: items => etiquetas[items[0].dataIndex], label: item => item.raw + ' ' + grafica.unidad } };
        return config;
    }
    const config = api.presets.barHorizontal(etiquetas.map((_, i) => String(i + 1)), valores, colores, comunes);
    config.options.scales.x.ticks.stepSize = Math.max(1, Math.ceil((grafica.maximo || 1) / 4));
    config.options.scales.x.ticks.maxTicksLimit = 5;
    config.options.plugins.tooltip.callbacks = {
        title: items => etiquetas[items[0]?.dataIndex] ?? '',
        label: item => item.raw + ' ' + grafica.unidad,
    };
    return config;
}

export function crearVisorGraficas() {
    return {
        graficasReporte: [], tipoGraficas: 'auto', cargandoGraficas: false, errorGraficas: '',
        prefijoGraficas: '', clavesGraficas: [], solicitudGraficas: null,

        iniciarGraficas() {
            this.prefijoGraficas = 'rm-reporte-' + crypto.randomUUID();
            this.$watch('tipoGraficas', () => this.$nextTick(() => this.dibujarGraficas()));
            this.$watch('graficasReporte', () => this.$nextTick(() => this.dibujarGraficas()));
        },
        claveGrafica(grafica) { return this.prefijoGraficas + '-' + grafica.id; },
        tipoParaGrafica(grafica) { return tipoDeGrafica(grafica, this.tipoGraficas); },
        porcentajeGrafica(punto, grafica) {
            return grafica.composicion && grafica.total > 0 && punto.valor !== null
                ? ' · ' + (punto.valor * 100 / grafica.total).toLocaleString('es', { maximumFractionDigits: 1 }) + ' %' : '';
        },
        formatoGrafica(valor) { return valor?.toLocaleString('es', { maximumFractionDigits: 1 }) ?? 'No disponible'; },
        colorGrafica(indice, grafica) {
            const tonos = grafica.tonos ?? tonosGrafica;
            return 'var(--rm-chart-' + tonos[indice % tonos.length] + '-500)';
        },
        dibujarGraficas() {
            const api = window.RMCharts;
            if (!api) return;
            const vigentes = [];
            for (const grafica of this.graficasReporte) {
                const clave = this.claveGrafica(grafica);
                const canvas = document.getElementById(clave);
                if (!canvas) continue;
                vigentes.push(clave);
                if (grafica.resumen?.solo_cifra || (grafica.composicion && grafica.total === 0)) {
                    api.destroy(clave);
                    continue;
                }
                const render = () => api.init(clave, canvas, configurarGraficaReporte(api, grafica, this.tipoParaGrafica(grafica)), render);
                render();
            }
            this.clavesGraficas.filter(clave => !vigentes.includes(clave)).forEach(clave => api.destroy(clave));
            this.clavesGraficas = vigentes;
        },
        async consultarGraficas(url) {
            this.urlGraficasActual = url;
            this.solicitudGraficas?.abort();
            const solicitud = new AbortController();
            this.solicitudGraficas = solicitud;
            this.cargandoGraficas = true;
            this.errorGraficas = '';
            try {
                const respuesta = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: solicitud.signal });
                if (!respuesta.ok) throw new Error([401, 403, 419].includes(respuesta.status)
                    ? 'Tu sesión o tus permisos cambiaron. Revisa tu acceso.' : 'No se pudieron actualizar las gráficas. Vuelve a intentarlo.');
                const datos = await respuesta.json();
                if (!solicitud.signal.aborted) this.graficasReporte = datos.graficas;
            } catch (error) {
                if (!solicitud.signal.aborted) this.errorGraficas = error instanceof TypeError || error instanceof SyntaxError
                    ? 'No se pudieron consultar las gráficas. Vuelve a intentarlo.' : error.message;
            } finally {
                if (this.solicitudGraficas === solicitud) {
                    this.cargandoGraficas = false;
                    this.$nextTick(() => this.dibujarGraficas());
                }
            }
        },
        destruirGraficas() {
            this.solicitudGraficas?.abort();
            this.clavesGraficas.forEach(clave => window.RMCharts?.destroy(clave));
            this.clavesGraficas = [];
        },
        reintentarGraficas() { if (this.urlGraficasActual) this.consultarGraficas(this.urlGraficasActual); },
    };
}

export function crearEstadisticasVista() {
    return {
        ...crearVisorGraficas(),
        init() {
            this.iniciarGraficas();
            if (this.$el.dataset.graficasIniciales) {
                this.graficasReporte = JSON.parse(this.$el.dataset.graficasIniciales);
            } else {
                this.$nextTick(() => this.consultarGraficas(this.$el.dataset.graficasUrl));
            }
        },
        destroy() { this.destruirGraficas(); },
    };
}

if (typeof window !== 'undefined') {
    window.rmReportStatistics = crearEstadisticasVista;
}
