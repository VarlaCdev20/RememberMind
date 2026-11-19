// Filtra únicamente la proyección autorizada del resultado guardado; no interpreta datos clínicos.
export function storedEvidence(rows) {
    const normalize = value => String(value).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
    return {
        rows, busqueda: '', tipo: '', pagina: 1, porPagina: 8,
        get filtradas() {
            const query = normalize(this.busqueda.trim());
            return this.rows.filter(row => (!this.tipo || row.tipo === this.tipo)
                && (!query || [row.tipo, row.dato, row.valor, row.fecha].some(value => normalize(value).includes(query))));
        },
        get paginas() { return Math.max(1, Math.ceil(this.filtradas.length / this.porPagina)); },
        get visibles() { return this.filtradas.slice((this.pagina - 1) * this.porPagina, this.pagina * this.porPagina); },
        get rango() {
            const total = this.filtradas.length;
            return total ? `${(this.pagina - 1) * this.porPagina + 1}–${Math.min(this.pagina * this.porPagina, total)} de ${total} datos` : '0 datos';
        },
        filtrar() { this.pagina = 1; },
        cambiarPagina(delta) { this.pagina = Math.max(1, Math.min(this.paginas, this.pagina + delta)); },
    };
}

if (typeof window !== 'undefined') window.rmStoredEvidence = storedEvidence;
