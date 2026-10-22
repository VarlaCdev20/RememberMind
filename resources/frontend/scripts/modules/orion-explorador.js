/** Visual interaction only. Nodes, edges, conditions and results are supplied by PHP. */
export function distribuirGrafo(datos, vertical = false) {
    const niveles = new Map();
    const nodos = datos.nodos.map(n => {
        const fila = niveles.get(n.columna) || 0;
        niveles.set(n.columna, fila + 1);
        return { ...n, x: 24 + (vertical ? fila : n.columna) * 220, y: 30 + (vertical ? n.columna : fila) * 120 };
    });
    const maxColumna = Math.max(0, ...nodos.map(n => n.columna));
    const filas = Math.max(0, ...niveles.values());
    return { nodos, vertical, ancho: Math.max(260, (vertical ? filas : maxColumna + 1) * 220 + 24),
        alto: Math.max(220, (vertical ? maxColumna + 1 : filas) * 120 + 35) };
}

export function exploradorGrafo(datos) {
    return {
        ...distribuirGrafo(datos), aristas: datos.aristas, seleccionado: null, escala: 1, dx: 0, dy: 0, arrastre: null,
        viewportAncho: 960, viewportAlto: 440, observador: null,
        init() {
            this.$nextTick(() => {
                if (!this.$refs.area) return;
                this.observador = new ResizeObserver(([entrada]) => {
                    const { width, height } = entrada.contentRect;
                    if (!width || !height) return;
                    this.viewportAncho = width;
                    this.viewportAlto = height;
                    Object.assign(this, distribuirGrafo(datos, width < 640));
                    this.restablecer();
                });
                this.observador.observe(this.$refs.area);
            });
        },
        destroy() { this.observador?.disconnect(); },
        get actual() { return this.nodos.find(n => n.id === this.seleccionado); },
        get reglaActual() { return (datos.reglas || []).find(r => r.id === this.seleccionado); },
        punto(id) { return this.nodos.find(n => n.id === id); },
        ruta(a) {
            const origen = this.punto(a.origen), destino = this.punto(a.destino);
            if (!origen || !destino) return '';
            return this.vertical
                ? `M ${origen.x + 95} ${origen.y + 86} L ${destino.x + 95} ${destino.y}`
                : `M ${origen.x + 190} ${origen.y + 43} L ${destino.x} ${destino.y + 43}`;
        },
        vinculada(a) { return a.origen === this.seleccionado || a.destino === this.seleccionado; },
        seleccionar(id) { this.seleccionado = id; this.enfocar(id); },
        enfocar(id) {
            const n = this.punto(id);
            if (!n) return;
            const x = n.x * this.escala + this.dx, y = n.y * this.escala + this.dy;
            if (x < 0 || x + 190 * this.escala > this.viewportAncho || y < 0 || y + 86 * this.escala > this.viewportAlto) {
                this.dx = this.viewportAncho / 2 - (n.x + 95) * this.escala;
                this.dy = this.viewportAlto / 2 - (n.y + 43) * this.escala;
            }
        },
        zoom(paso) { this.escala = Math.min(2.5, Math.max(0.5, this.escala + paso)); },
        centrar() {
            this.escala = Math.max(0.5, Math.min(1, this.viewportAncho / this.ancho, this.viewportAlto / this.alto));
            this.dx = (this.viewportAncho - this.ancho * this.escala) / 2;
            this.dy = (this.viewportAlto - this.alto * this.escala) / 2;
        },
        restablecer() { this.escala = 1; this.dx = this.dy = 0; },
        iniciar(evento) {
            if (evento.target.closest('[data-orion-node]')) return;
            this.arrastre = { x: evento.clientX - this.dx, y: evento.clientY - this.dy };
            evento.currentTarget.setPointerCapture(evento.pointerId);
        },
        mover(evento) { if (this.arrastre) { this.dx = evento.clientX - this.arrastre.x; this.dy = evento.clientY - this.arrastre.y; } },
        terminar() { this.arrastre = null; },
    };
}

export function graficaCobertura(datos, clave) {
    return {
        datos, clave,
        init() { this.$nextTick(() => this.dibujar()); },
        dibujar() {
            const api = window.RMCharts;
            if (!api || !this.$refs.canvas) return;
            const config = api.presets.barHorizontal(this.datos.etiquetas, this.datos.valores, this.datos.etiquetas.map(() => api.color('cognitive')));
            config.data.datasets[0].label = 'Evidencias conservadas';
            config.options.scales.x.beginAtZero = true;
            config.options.scales.x.ticks = { ...config.options.scales.x.ticks, precision: 0 };
            api.init(this.clave, this.$refs.canvas, config, () => this.dibujar());
        },
        destroy() { window.RMCharts?.destroy(this.clave); },
    };
}

if (typeof window !== 'undefined') {
    window.rmOrionGraph = exploradorGrafo;
    window.rmOrionCoverage = graficaCobertura;
    window.rmCognitiveTimeline = cognitiveTimeline;
}

/** DTO numérico autorizado. Eje temporal real; ninguna interpretación clínica en el cliente. */
export function cognitiveTimeline(puntos, clave, unidad) {
    return {
        activo: null,
        get puntoActivo() { return puntos[this.activo] ?? null; },
        seleccionar(index) { if (Number.isInteger(index) && puntos[index]) this.activo = index; },
        init() { this.$nextTick(() => this.dibujar()); },
        dibujar() {
            if (!puntos.length || !this.$refs.canvas || !window.RMCharts) return;
            const api = window.RMCharts;
            const fechas = new Intl.DateTimeFormat('es', { day: '2-digit', month: '2-digit' });
            const color = api.color('cognitive', 'strong');
            const config = api.presets.line([], [{ label: unidad, data: puntos.map(p => ({x: Date.parse(p.fecha), y: p.valor})), borderColor: color, backgroundColor: color, pointRadius: 5, tension: 0, fill: false, spanGaps: false, showLine: puntos.length > 1 }]);
            config.options.parsing = false;
            config.options.scales.x = { type: 'linear', title: {display: true, text: 'Fecha del registro'}, ticks: { maxTicksLimit: 5, callback: value => fechas.format(new Date(value)) } };
            config.options.scales.y = { ...config.options.scales.y, beginAtZero: false, title: {display: true, text: unidad} };
            config.options.plugins.datalabels = {display: false};
            config.options.plugins.tooltip = { ...config.options.plugins.tooltip, callbacks: { title: items => new Date(items[0].parsed.x).toLocaleString('es'), label: item => `${item.parsed.y} ${unidad}` } };
            config.options.onHover = (event, elements) => { if (elements.length) this.seleccionar(elements[0].index); };
            config.options.onClick = (event, elements) => { if (elements.length) this.seleccionar(elements[0].index); };
            api.init(clave, this.$refs.canvas, config, () => this.dibujar());
        },
        destroy() { window.RMCharts?.destroy(clave); },
    };
}
