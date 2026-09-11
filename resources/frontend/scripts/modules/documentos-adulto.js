export default function documentosAdulto(fechaActual) {
    return {
        modalDocumento: false,
        isEditingDoc: false,
        docData: {},
        errors: {},
        cargando: false,

        abrirRegistro() {
            this.isEditingDoc = false;
            this.docData = {
                id: null, nom_doc: '', tipo_doc: '',
                fecha_doc: fechaActual, observaciones: '',
            };
            this.errors = {};
            this.cargando = false;
            this.modalDocumento = true;
        },

        abrirEdicion(doc) {
            this.isEditingDoc = true;
            this.docData = {
                id: doc.cod_doc_am,
                nom_doc: doc.nombre || doc.nom_doc,
                tipo_doc: doc.tipo_documento || doc.tipo_doc,
                fecha_doc: (doc.fecha_subida || doc.fecha_doc || '').slice(0, 10),
                observaciones: doc.observaciones || '',
            };
            this.errors = {};
            this.cargando = false;
            this.modalDocumento = true;
        },

        cerrarModal() {
            this.modalDocumento = false;
            this.errors = {};
        },

        async validarDocumento(event) {
            event.preventDefault();
            if (this.cargando) return;

            this.errors = {};
            const form = event.target;
            const nombre = form.querySelector('[name=nombre]')?.value || '';
            const tipo = form.querySelector('[name=tipo_documento]')?.value || '';

            if (!nombre.trim()) {
                this.errors.nom_doc = 'Debe escribir el título del documento.';
            }
            if (!tipo) {
                this.errors.tipo_doc = 'Debe seleccionar el tipo de documento.';
            }

            if (!this.isEditingDoc) {
                const archivo = form.querySelector('[name=archivo]')?.files?.[0];
                if (!archivo) {
                    this.errors.archivo = 'Debe adjuntar un archivo para el registro nuevo.';
                } else {
                    const extension = archivo.name.split('.').pop().toLowerCase();
                    if (!['pdf', 'jpg', 'jpeg', 'png', 'webp'].includes(extension)) {
                        this.errors.archivo = 'El archivo debe ser PDF o imagen (JPG, PNG, WEBP).';
                    }
                    if (archivo.size > 5 * 1024 * 1024) {
                        this.errors.archivo = 'El archivo no puede pesar más de 5MB.';
                    }
                }
            }

            if (Object.keys(this.errors).length) {
                await globalThis.Swal.fire({
                    icon: 'error',
                    title: 'Formulario incompleto',
                    text: 'Revise los campos marcados antes de continuar.',
                    confirmButtonText: 'Entendido',
                });
                return;
            }

            const result = await globalThis.Swal.fire({
                title: 'Confirmar acción',
                text: this.isEditingDoc
                    ? '¿Desea actualizar los metadatos de este documento?'
                    : '¿Desea subir este nuevo documento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
            });
            if (result.isConfirmed) {
                this.cargando = true;
                form.submit();
            }
        },
    };
}
