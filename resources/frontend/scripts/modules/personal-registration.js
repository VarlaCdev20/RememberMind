export function crearRegistroPersonal() {
    return {
        dirty: false,
        archivosLocales: {},
        vistaDocumento: null,
        normalizar(event) {
            const campo = event.target, tipo = campo.dataset.captura;
            if (!tipo || !campo.value) return;
            const cursor = campo.selectionStart, original = campo.value;
            const filtrar = valor => tipo === 'numero' ? valor.replace(/[^0-9]/g, '')
                : tipo === 'documento' ? valor.toLocaleUpperCase('es').replace(/[^A-Z0-9]/g, '')
                : tipo === 'run' ? valor.toLocaleUpperCase('es').replace(/[^0-9K-]/g, '')
                : tipo === 'letras' ? valor.replace(/[^\p{L}\p{M}\s'’\-]/gu, '').toLocaleUpperCase('es') : valor.toLocaleUpperCase('es');
            const valor = filtrar(original);
            if (valor !== original) {
                campo.value = valor;
                if (cursor !== null) { const posicion = filtrar(original.slice(0, cursor)).length; campo.setSelectionRange(posicion, posicion); }
            }
        },
        retenerArchivo(event) {
            const codigo = event.target.dataset.documentoPersonal;
            if (!codigo) return;
            this.quitarArchivoLocal(codigo);
            const archivo = event.target.files?.[0];
            if (archivo) this.archivosLocales[codigo] = archivo;
        },
        abrirVistaDocumento(boton) {
            const { documentoCodigo: codigo, documentoMime: mime, documentoNombre: nombre } = boton.dataset;
            const archivo = this.archivosLocales[codigo];
            if (!archivo || !['application/pdf', 'image/jpeg', 'image/png', 'image/webp'].includes(mime)) return;
            this.cerrarVistaDocumento(false);
            this.vistaDocumento = { codigo, nombre, mime, url: URL.createObjectURL(new Blob([archivo], { type: mime })) };
            this.$nextTick?.(() => {
                document.getElementById('personal-vista-documento')?.scrollIntoView({ block: 'start', inline: 'nearest' });
                document.getElementById('personal-cerrar-vista-documento')?.focus({ preventScroll: true });
            });
        },
        cerrarVistaDocumento(enfocar = true) {
            const vista = this.vistaDocumento;
            if (vista?.url) URL.revokeObjectURL(vista.url);
            this.vistaDocumento = null;
            if (enfocar && vista && typeof document !== 'undefined') document.getElementById('vista-documento-'+vista.codigo)?.focus();
        },
        quitarArchivoLocal(codigo) {
            if (this.vistaDocumento?.codigo === codigo) this.cerrarVistaDocumento(false);
            delete this.archivosLocales[codigo];
        },
        actualizarCatalogo(codigos) {
            for (const codigo of Object.keys(this.archivosLocales)) if (!codigos.includes(codigo)) this.quitarArchivoLocal(codigo);
        },
        destroy() { this.cerrarVistaDocumento(false); this.archivosLocales = {}; },
    };
}

if (typeof window !== 'undefined') window.rmPersonalRegistration = crearRegistroPersonal;
