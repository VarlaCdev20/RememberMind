import { distribuirTraza, iconoTraza, presentacionesTraza } from './alertas-distribucion.js';

/** Exploración y organización local; nunca modifica datos del dominio. */
export function rmTrazaAlerta(datos) {
    return {
        datos, modo: 'mapa', capa: 'todas', seleccionado: 'alerta', destacado: null, zoom: 1, panX: 0, panY: 0,
        coordenadas: {}, altura: 440, organizar: false, arrastre: null, observador: null, aspecto: 1,
        marco: { x: 0, y: 0, ancho: 900, alto: 900 }, pantallaCompleta: false, cambiandoPantalla: false, abriendoDetalle: false, aviso: '',
        init() {
            this.coordenadas = Object.fromEntries(Object.entries(this.datos.posiciones || distribuirTraza(this.datos)).map(([id, p]) => [id, { ...p }]));
            const inicial = this.datos.inicio || 'alerta';
            this.seleccionado = this.datos.nodos.some(n => n.id === inicial) ? inicial : this.datos.nodos[0]?.id;
            this.centrar();
            if (typeof ResizeObserver !== 'undefined' && this.$refs?.lienzo) {
                this.observador = new ResizeObserver(([entrada]) => {
                    if (entrada.contentRect.width > 0 && entrada.contentRect.height > 0) {
                        this.aspecto = entrada.contentRect.width / entrada.contentRect.height;
                        this.centrar();
                    }
                });
                this.observador.observe(this.$refs.lienzo);
            }
        },
        destroy() { this.observador?.disconnect(); },
        get presentacion() { return presentacionesTraza[this.datos.foco] || presentacionesTraza.auditoria; },
        icono(nodo) { return iconoTraza(nodo, this.datos); },
        get silueta() {
            return { red: 'M 32 0 A 32 32 0 1 0 -32 0 A 32 32 0 1 0 32 0',
                capacidad: 'M -24 -30 L 24 -30 L 44 0 L 24 30 L -24 30 L -44 0 Z',
                balance: 'M -22 -30 L 22 -30 Q 44 -30 44 0 Q 44 30 22 30 L -22 30 Q -44 30 -44 0 Q -44 -30 -22 -30 Z',
                actividad: 'M -30 -30 L 30 -30 Q 44 -30 44 -16 L 44 16 Q 44 30 30 30 L -30 30 Q -44 30 -44 16 L -44 -16 Q -44 -30 -30 -30 Z',
            }[this.presentacion.forma] || 'M -32 -30 L 32 -30 Q 42 -30 42 -20 L 42 20 Q 42 30 32 30 L -32 30 Q -42 30 -42 20 L -42 -20 Q -42 -30 -32 -30 Z';
        },
        get nodo() { return this.datos.nodos.find(n => n.id === this.seleccionado); },
        get vista() {
            const w = this.marco.ancho / this.zoom, h = this.marco.alto / this.zoom;
            return `${this.marco.x + (this.marco.ancho - w) / 2 + this.panX} ${this.marco.y + (this.marco.alto - h) / 2 + this.panY} ${w} ${h}`;
        },
        get relacionesSeleccionadas() {
            return this.datos.aristas.filter(a => a.desde === this.seleccionado || a.hasta === this.seleccionado)
                .map(a => `${this.datos.nodos.find(n => n.id === a.desde)?.label || ''} · ${a.label || 'relación'} · ${this.datos.nodos.find(n => n.id === a.hasta)?.label || ''}`);
        },
        posicion(id) {
            return this.coordenadas[id] || { x: 450, y: 100 };
        },
        nodoVisible(nodo) { return !!nodo && (this.capa === 'todas' || ['alerta', 'residente'].includes(nodo.id) || (nodo.grupo || 'caso') === this.capa); },
        aristaVisible(arista) { return this.nodoVisible(this.datos.nodos.find(n => n.id === arista.desde)) && this.nodoVisible(this.datos.nodos.find(n => n.id === arista.hasta)); },
        cambiarCapa(capa) {
            if (!['todas', 'caso', 'profesional', 'origen', 'eventos'].includes(capa)) return;
            this.capa = capa;
            if (!this.nodoVisible(this.nodo)) this.seleccionado = this.datos.nodos.find(n => this.nodoVisible(n))?.id;
            this.destacado = null;
            this.centrar();
        },
        seleccionar(id) { if (this.nodoVisible(this.datos.nodos.find(n => n.id === id))) this.seleccionado = id; },
        conectado(arista) { const id = this.destacado || this.seleccionado; return arista.desde === id || arista.hasta === id; },
        ampliar(delta) { this.zoom = Math.min(4, Math.max(0.6, Math.round((this.zoom + delta) * 10) / 10)); },
        centrar() {
            const puntos = this.datos.nodos.filter(n => this.nodoVisible(n)).map(n => this.posicion(n.id));
            if (!puntos.length) return;
            const x = Math.min(...puntos.map(p => p.x)) - 110, y = Math.min(...puntos.map(p => p.y)) - 85;
            const w = Math.max(...puntos.map(p => p.x)) + 110 - x, h = Math.max(...puntos.map(p => p.y)) + 95 - y;
            const ancho = Math.max(w, h * this.aspecto), alto = Math.max(h, w / this.aspecto);
            this.marco = { x: x - (ancho - w) / 2, y: y - (alto - h) / 2, ancho, alto };
            this.zoom = 1; this.panX = this.panY = 0;
        },
        restablecer() { this.coordenadas = distribuirTraza(this.datos); this.centrar(); this.aviso = 'Distribución del área restablecida. Los registros se conservan.'; },
        centrarSeleccion() {
            if (!this.nodo) return;
            const posicion = this.posicion(this.seleccionado);
            this.zoom = Math.min(4, Math.max(1.6, this.marco.ancho / 420)); this.panX = posicion.x - (this.marco.x + this.marco.ancho / 2); this.panY = posicion.y - (this.marco.y + this.marco.alto / 2);
        },
        consultarSeleccion() {
            this.$refs.inspector?.focus({ preventScroll: true });
            this.$refs.inspector?.scrollIntoView({ block: 'nearest', behavior: 'instant' });
        },
        enfocarNodo(id) {
            this.destacado = id;
            if (this.zoom > 1) {
                const posicion = this.posicion(id);
                this.panX = posicion.x - (this.marco.x + this.marco.ancho / 2); this.panY = posicion.y - (this.marco.y + this.marco.alto / 2);
            }
        },
        iniciarArrastre(evento) {
            if (this.arrastre) return;
            if (evento.button !== undefined && evento.button !== 0) return;
            const elemento = evento.target.closest('[data-traza-nodo]');
            const id = elemento?.dataset.trazaNodo;
            if (id && !this.organizar) return;
            if (id) this.seleccionar(id);
            const rect = evento.currentTarget.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            this.arrastre = { puntero: evento.pointerId, id, x: evento.clientX, y: evento.clientY, panX: this.panX, panY: this.panY,
                escalaX: this.marco.ancho / this.zoom / rect.width, escalaY: this.marco.alto / this.zoom / rect.height, posicion: id ? { ...this.posicion(id) } : null };
            evento.currentTarget.setPointerCapture(evento.pointerId);
        },
        arrastrar(evento) {
            const a = this.arrastre;
            if (!a || a.puntero !== evento.pointerId) return;
            const dx = (evento.clientX - a.x) * a.escalaX, dy = (evento.clientY - a.y) * a.escalaY;
            if (a.id) this.coordenadas[a.id] = { x: a.posicion.x + dx, y: a.posicion.y + dy };
            else { this.panX = a.panX - dx; this.panY = a.panY - dy; }
        },
        terminarArrastre(evento) { if (this.arrastre?.puntero === evento.pointerId) this.arrastre = null; },
        async alternarPantallaCompleta() {
            if (this.cambiandoPantalla) return;
            this.cambiandoPantalla = true;
            this.aviso = '';
            try {
                if (document.fullscreenElement === this.$root) await document.exitFullscreen();
                else if (this.$root.requestFullscreen) await this.$root.requestFullscreen();
                else this.aviso = 'Este navegador no permite ampliar a pantalla completa. Puedes ajustar la altura y el acercamiento.';
            } catch {
                this.aviso = 'No se pudo ampliar a pantalla completa. Puedes seguir ajustando la altura y el acercamiento.';
            } finally { this.cambiandoPantalla = false; }
        },
        async consultarDetalle() {
            if (this.abriendoDetalle) return;
            this.abriendoDetalle = true;
            try {
                if (document.fullscreenElement === this.$root) await document.exitFullscreen();
                await this.$wire.verDetalle(this.datos.codigo);
            } catch {
                this.aviso = 'No se pudo abrir el detalle. Conserva esta vista y vuelve a intentarlo.';
            } finally { this.abriendoDetalle = false; }
        },
        mover(evento, id) {
            if (!['ArrowRight', 'ArrowDown', 'ArrowLeft', 'ArrowUp'].includes(evento.key)) return;
            evento.preventDefault();
            if (evento.altKey && this.organizar) {
                const posicion = this.posicion(id);
                this.coordenadas[id] = { x: posicion.x + (evento.key === 'ArrowRight' ? 20 : evento.key === 'ArrowLeft' ? -20 : 0),
                    y: posicion.y + (evento.key === 'ArrowDown' ? 20 : evento.key === 'ArrowUp' ? -20 : 0) };
                return;
            }
            const nodos = [...this.$root.querySelectorAll('[data-traza-nodo]')].filter(n => this.nodoVisible(this.datos.nodos.find(d => d.id === n.dataset.trazaNodo)));
            const i = nodos.findIndex(n => n.dataset.trazaNodo === id);
            nodos[(i + (['ArrowRight', 'ArrowDown'].includes(evento.key) ? 1 : -1) + nodos.length) % nodos.length]?.focus();
        },
    };
}
if (typeof window !== 'undefined') window.rmTrazaAlerta = rmTrazaAlerta;
