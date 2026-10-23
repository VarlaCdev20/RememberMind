import { knowledgeGraph } from './orion-explorador.js';

/** Local navigation only: all relationships and clinical states arrive from PHP. */
export function residentWorkspace(elementos) {
    return {
        elementos, grupo: elementos[0]?.grupo ?? 'areas', seleccionado: elementos[0]?.id ?? null,
        get opciones() { return this.elementos.filter(e => e.grupo === this.grupo); },
        get actual() { return this.elementos.find(e => e.id === this.seleccionado) ?? null; },
        cambiarGrupo(grupo) {
            if (!['areas', 'requisitos', 'fuentes'].includes(grupo) || !this.elementos.some(e => e.grupo === grupo)) return;
            this.grupo = grupo;
            this.seleccionado = this.opciones[0]?.id ?? null;
        },
        seleccionar(id) {
            if (this.opciones.some(e => e.id === id)) this.seleccionado = id;
        },
    };
}

export function residentGraph(datos) {
    const base = knowledgeGraph(datos);
    const iniciarVisor = base.init;
    return Object.defineProperties(base, Object.getOwnPropertyDescriptors({
        vista: 'mapa', etapa: null, recorrido: [],
<<<<<<< HEAD
=======
        paginaFuentes: 1, fuentesPorPagina: 6,
        get fuentesPaginadas() { return this.nodos.filter(n => n.columna === 0); },
        get paginarFuentes() { return this.nodos.some(n => n.tipo === 'Conjunto de registros') && this.fuentesPaginadas.length > this.fuentesPorPagina; },
        get paginasFuentes() { return Math.max(1, Math.ceil(this.fuentesPaginadas.length / this.fuentesPorPagina)); },
        esVisible(id) {
            const indice = this.fuentesPaginadas.findIndex(n => n.id === id);
            const enPagina = !this.paginarFuentes || indice < 0 || Math.floor(indice / this.fuentesPorPagina) + 1 === this.paginaFuentes;
            return enPagina && (!this.focoVecindario || this.esVecino(id));
        },
        mostrarFuente(id) {
            const indice = this.fuentesPaginadas.findIndex(n => n.id === id);
            if (this.paginarFuentes && indice >= 0) this.paginaFuentes = Math.floor(indice / this.fuentesPorPagina) + 1;
        },
        cambiarPaginaFuente(pagina) {
            if (!Number.isInteger(pagina) || pagina < 1 || pagina > this.paginasFuentes) return;
            this.paginaFuentes = pagina;
            this.seleccionado = this.nodos.find(n => n.columna === 2)?.id ?? null;
            this.recorrido = this.seleccionado ? [this.seleccionado] : [];
            this.focoVecindario = false;
            this.distribuir();
        },
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        init() {
            this.vista = window.matchMedia('(max-width: 640px)').matches ? 'lista' : 'mapa';
            iniciarVisor.call(this);
            this.seleccionado = datos.nodos.find(n => n.columna === 2)?.id ?? null;
            this.recorrido = this.seleccionado ? [this.seleccionado] : [];
        },
        restablecer() { this.distribuir(); },
        get conexionesActuales() {
            return this.aristas.filter(a => this.vinculada(a)).map(a => ({
                ...a, nombre: this.punto(a.origen === this.seleccionado ? a.destino : a.origen)?.nombre ?? 'Fuente no disponible',
            }));
        },
        get pasosRecorrido() { return this.recorrido.map(id => this.punto(id)).filter(Boolean); },
        seleccionar(id) {
            if (!this.punto(id)) return;
<<<<<<< HEAD
            this.seleccionado = id;
            this.recorrido = [id];
            this.etapa = null;
            if (this.focoVecindario) this.distribuir();
=======
            const paginaPrevia = this.paginaFuentes;
            this.mostrarFuente(id);
            this.seleccionado = id;
            this.recorrido = [id];
            this.etapa = null;
            if (this.focoVecindario || paginaPrevia !== this.paginaFuentes) this.distribuir();
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            this.escala = Math.max(.65, this.escala);
            this.enfocar(id);
        },
        seguirConexion(id) {
            if (!this.aristas.some(a => (a.origen === this.seleccionado && a.destino === id) || (a.destino === this.seleccionado && a.origen === id))) return;
<<<<<<< HEAD
=======
            const paginaPrevia = this.paginaFuentes;
            this.mostrarFuente(id);
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            const anterior = this.recorrido.indexOf(id);
            this.recorrido = anterior >= 0 ? this.recorrido.slice(0, anterior + 1) : [...this.recorrido, id];
            this.seleccionado = id;
            this.etapa = null;
<<<<<<< HEAD
            if (this.focoVecindario) this.distribuir();
=======
            if (this.focoVecindario || paginaPrevia !== this.paginaFuentes) this.distribuir();
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            this.enfocar(id);
        },
        volverPaso(id) {
            const indice = this.recorrido.indexOf(id);
            if (indice < 0) return;
<<<<<<< HEAD
            this.recorrido = this.recorrido.slice(0, indice + 1);
            this.seleccionado = id;
            if (this.focoVecindario) this.distribuir();
=======
            const paginaPrevia = this.paginaFuentes;
            this.mostrarFuente(id);
            this.recorrido = this.recorrido.slice(0, indice + 1);
            this.seleccionado = id;
            if (this.focoVecindario || paginaPrevia !== this.paginaFuentes) this.distribuir();
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
            this.enfocar(id);
        },
        enRecorrido(a) {
            return this.recorrido.some((id, i) => i > 0 && ((id === a.destino && this.recorrido[i - 1] === a.origen) || (id === a.origen && this.recorrido[i - 1] === a.destino)));
        },
        explorarEtapa(tipo) {
            const nodo = this.nodos.find(n => tipo === 'Elemento' ? n.columna === 2 : n.tipo === tipo || (tipo === 'Soporte' && n.tipo === 'Registro'));
            if (!nodo) return;
            this.seleccionar(nodo.id);
            this.etapa = tipo;
        },
        disponible(tipo) { return this.nodos.some(n => tipo === 'Elemento' ? n.columna === 2 : n.tipo === tipo || (tipo === 'Soporte' && n.tipo === 'Registro')); },
        cambiarVista(vista) {
            if (!['mapa', 'lista'].includes(vista)) return;
            this.vista = vista;
            if (vista === 'mapa') this.$nextTick(() => this.centrar());
        },
    }));
}

if (typeof window !== 'undefined') {
    window.rmExpertResidentWorkspace = residentWorkspace;
    window.rmExpertResidentGraph = residentGraph;
}
