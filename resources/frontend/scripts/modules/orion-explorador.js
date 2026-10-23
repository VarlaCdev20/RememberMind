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
            this.escala = Math.min(1, this.viewportAncho / this.ancho, this.viewportAlto / this.alto);
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

/** Interacción del conocimiento: reutiliza el visor histórico, sin compartir contratos clínicos. */
export function distribuirRedCompacta(datos, ancho = 960) {
    const columnas = Math.max(1, Math.min(6, Math.floor(ancho / 220)));
    const nodos = datos.nodos.map((n, i) => ({ ...n, x: 24 + (i % columnas) * 220, y: 24 + Math.floor(i / columnas) * 120 }));
    return { nodos, vertical: false, ancho: Math.min(columnas, Math.max(1, nodos.length)) * 220 + 24,
        alto: Math.max(220, Math.ceil(nodos.length / columnas) * 120 + 24) };
}

export function knowledgeGraph(datos, seleccionado = null) {
    const visor = exploradorGrafo(datos);
    return Object.defineProperties(visor, Object.getOwnPropertyDescriptors({
        seleccionado, arrastreNodo: null,
        distribucion: datos.nodos.length > 12 ? 'compacta' : 'niveles', focoVecindario: false,
        get vecinos() {
            const ids = new Set(this.aristas.filter(a => this.vinculada(a)).flatMap(a => [a.origen, a.destino]));
            return ids;
        },
        get numeroConexiones() { return this.aristas.filter(a => this.vinculada(a)).length; },
        esVecino(id) { return !this.seleccionado || id === this.seleccionado || this.vecinos.has(id); },
        esVisible(id) { return !this.focoVecindario || this.esVecino(id); },
        aristaVisible(a) { return this.esVisible(a.origen) && this.esVisible(a.destino); },
        get cantidadVisible() { return this.nodos.filter(n => this.esVisible(n.id)).length; },
        distribuir(modo = this.distribucion) {
            if (!['compacta', 'niveles'].includes(modo)) return;
            this.distribucion = modo;
            const entrada = { nodos: datos.nodos.filter(n => this.esVisible(n.id)) };
            const layout = modo === 'compacta' ? distribuirRedCompacta(entrada, this.viewportAncho) : distribuirGrafo(entrada, this.viewportAncho < 640);
            const posiciones = new Map(layout.nodos.map(n => [n.id, n]));
            this.nodos = this.nodos.map(n => posiciones.get(n.id) ?? n);
            this.ancho = layout.ancho; this.alto = layout.alto; this.vertical = layout.vertical;
            this.centrar();
        },
        restablecer() { this.distribuir(); },
        enfocarConexiones() {
            if (!this.seleccionado) return;
            this.focoVecindario = !this.focoVecindario;
            this.distribuir();
        },
        limpiarSeleccion() { this.seleccionado = null; this.focoVecindario = false; this.distribuir(); },
        seleccionar(id) {
            if (!this.punto(id)) return;
            this.seleccionado = id;
            if (this.focoVecindario) this.distribuir();
            this.escala = Math.max(.65, this.escala);
            this.enfocar(id);
        },
        iniciarNodo(evento, id) {
            if (evento.button !== 0) return;
            const nodo = this.punto(id);
            if (!nodo) return;
            this.arrastreNodo = { id, x: evento.clientX, y: evento.clientY, nx: nodo.x, ny: nodo.y };
            evento.currentTarget?.setPointerCapture(evento.pointerId);
        },
        mover(evento) {
            if (this.arrastreNodo) {
                const n = this.punto(this.arrastreNodo.id);
                n.x = this.arrastreNodo.nx + (evento.clientX - this.arrastreNodo.x) / this.escala;
                n.y = this.arrastreNodo.ny + (evento.clientY - this.arrastreNodo.y) / this.escala;
            } else if (this.arrastre) {
                this.dx = evento.clientX - this.arrastre.x;
                this.dy = evento.clientY - this.arrastre.y;
            }
        },
        terminar() { this.arrastre = this.arrastreNodo = null; },
        etiqueta(a) {
            const o = this.punto(a.origen), d = this.punto(a.destino);
            return o && d ? { x: (o.x + d.x) / 2 + 95, y: (o.y + d.y) / 2 + 36 } : { x: 0, y: 0 };
        },
    }));
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
    window.rmExpertKnowledgeGraph = knowledgeGraph;
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
