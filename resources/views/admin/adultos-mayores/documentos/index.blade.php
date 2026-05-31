@use('Carbon\Carbon')
@use('Illuminate\Support\Facades\Storage')

<x-sistema-layout>
    @php
        $nombreCompleto = trim("{$adulto_mayor->nombres} {$adulto_mayor->ap_paterno} {$adulto_mayor->ap_materno}");
        $documentosActivos = $adulto_mayor->documentos->whereNull('deleted_at');
        $documentosArchivados = $adulto_mayor->documentos->whereNotNull('deleted_at');
    @endphp

    <div
        x-data="{
            modalDocumento: false,
            isEditingDoc: false,
            docData: {},
            errors: {},
            cargando: false,
            
            abrirRegistro() {
                this.isEditingDoc = false;
                this.docData = { id: null, nom_doc: '', tipo_doc: '', fecha_doc: '{{ date('Y-m-d') }}', observaciones: '' };
                this.errors = {};
                this.modalDocumento = true;
            },
            
            abrirEdicion(doc) {
                this.isEditingDoc = true;
                this.docData = {
                    id: doc.cod_doc,
                    nom_doc: doc.nom_doc || doc.titulo,
                    tipo_doc: doc.tipo_doc || doc.tipo_documento,
                    fecha_doc: doc.fecha_doc || doc.fecha_emision || '',
                    observaciones: doc.observaciones || ''
                };
                this.errors = {};
                this.modalDocumento = true;
            },
            
            cerrarModal() {
                this.modalDocumento = false;
                this.errors = {};
            },

            validarDocumento(e) {
                e.preventDefault();
                this.errors = {};
                const form = e.target;
                const nom_doc = form.querySelector('[name=nom_doc]')?.value || '';
                const tipo_doc = form.querySelector('[name=tipo_doc]')?.value || '';
                
                if (!nom_doc || nom_doc.trim() === '') {
                    this.errors.nom_doc = 'Debe escribir el título del documento.';
                }
                if (!tipo_doc) {
                    this.errors.tipo_doc = 'Debe seleccionar el tipo de documento.';
                }
                
                if (!this.isEditingDoc) {
                    const archivoInput = form.querySelector('[name=archivo]');
                    if (!archivoInput || !archivoInput.files || archivoInput.files.length === 0) {
                        this.errors.archivo = 'Debe adjuntar un archivo para el registro nuevo.';
                    } else {
                        const file = archivoInput.files[0];
                        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
                        const fileExt = file.name.split('.').pop().toLowerCase();
                        if (!allowedExtensions.includes(fileExt)) {
                            this.errors.archivo = 'El archivo debe ser PDF o imagen (JPG, PNG).';
                        }
                        if (file.size > 5 * 1024 * 1024) { // 5MB
                            this.errors.archivo = 'El archivo no puede pesar más de 5MB.';
                        }
                    }
                }

                if (Object.keys(this.errors).length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Formulario incompleto',
                        text: 'Revise los campos marcados antes de continuar.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#2F3E5C'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirmar acción',
                    text: this.isEditingDoc ? '¿Desea actualizar los metadatos de este documento?' : '¿Desea subir este nuevo documento?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#2F3E5C',
                    cancelButtonColor: '#D5C7B9',
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.cargando = true;
                        form.submit();
                    }
                });
            }
        }"
        class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6"
    >
        {{-- CABECERA GESTIÓN DOCUMENTAL --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-[#2F3E5C]">Gestión Documental</h1>
                <p class="text-sm font-bold text-[#2F3E5C]/60 uppercase tracking-widest mt-1">
                    Expediente de: <span class="text-[#E27D60]">{{ $nombreCompleto }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'documentos']) }}" class="inline-flex items-center gap-2 rounded-xl border border-[#CBBBAA] bg-white px-4 py-2 text-xs font-black text-[#2F3E5C] transition hover:bg-[#F2EBE3]">
                    <i class="ph-bold ph-arrow-left"></i> Volver a Ficha
                </a>
                <button type="button" @click="abrirRegistro()" class="inline-flex items-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-[#1F2E4C]">
                    <i class="ph-bold ph-upload-simple text-base"></i> Registrar Documento
                </button>
            </div>
        </div>

        {{-- MÉTRICAS RESUMEN --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50">Total Documentos</p>
                <p class="text-2xl font-black text-[#2F3E5C] mt-1">{{ $adulto_mayor->documentos->count() }}</p>
            </div>
            <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50">Archivos Activos</p>
                <p class="text-2xl font-black text-[#617453] mt-1">{{ $documentosActivos->count() }}</p>
            </div>
            <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50">Archivados</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $documentosArchivados->count() }}</p>
            </div>
        </div>

        {{-- TABLA DE DOCUMENTOS ACTIVOS --}}
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#CBBBAA]/30 bg-[#F2EBE3]/30">
                <h2 class="text-sm font-black text-[#2F3E5C] uppercase tracking-widest">Documentos del Expediente</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#F2EBE3]/50 text-xs font-black uppercase tracking-wide text-[#2F3E5C]/60">
                        <tr>
                            <th class="px-6 py-4">Documento</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4">Fecha Subida</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#CBBBAA]/20">
                        @forelse($adulto_mayor->documentos as $doc)
                            <tr class="transition hover:bg-[#F2EBE3]/20 {{ $doc->trashed() ? 'opacity-60 bg-gray-50' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C]">
                                            @if(in_array(strtolower(pathinfo($doc->ruta_archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))
                                                <i class="ph-bold ph-image text-xl"></i>
                                            @else
                                                <i class="ph-bold ph-file-pdf text-xl"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-black text-[#2F3E5C]">{{ $doc->titulo ?? $doc->nom_doc }}</p>
                                            <p class="text-xs text-[#2F3E5C]/60 truncate max-w-xs">{{ $doc->observaciones ?? 'Sin observación' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-md bg-[#F2EBE3] px-2 py-1 text-xs font-black uppercase tracking-wide text-[#2F3E5C]">
                                        {{ $doc->tipo_documento ?? $doc->tipo_doc ?? 'Documento' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold">{{ $doc->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    @if($doc->trashed())
                                        <span class="text-xs font-black text-amber-600">ARCHIVADO</span>
                                    @else
                                        <span class="text-xs font-black text-[#617453]">ACTIVO</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($doc->trashed())
                                            <form action="{{ route('admin.adultos-mayores.documentos.restore', ['adulto_mayor' => $adulto_mayor->cod_am, 'documento' => $doc->cod_doc ?? $doc->id]) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="rounded-lg p-2 text-amber-600 hover:bg-amber-50" title="Restaurar Documento">
                                                    <i class="ph-bold ph-arrow-u-up-left text-lg"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ Storage::url($doc->ruta_archivo) }}" target="_blank" class="rounded-lg p-2 text-[#2F3E5C]/60 hover:bg-[#F2EBE3] hover:text-[#2F3E5C]" title="Ver/Descargar">
                                                <i class="ph-bold ph-download-simple text-lg"></i>
                                            </a>
                                            <button type="button" @click='abrirEdicion(@json($doc))' class="rounded-lg p-2 text-[#2F3E5C]/60 hover:bg-[#F2EBE3] hover:text-[#2F3E5C]" title="Editar Información">
                                                <i class="ph-bold ph-pencil-simple text-lg"></i>
                                            </button>
                                            <form action="{{ route('admin.adultos-mayores.documentos.destroy', ['adulto_mayor' => $adulto_mayor->cod_am, 'documento' => $doc->cod_doc ?? $doc->id]) }}" method="POST" class="inline" onsubmit="return confirm('¿Archivar este documento?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="rounded-lg p-2 text-red-500 hover:bg-red-50" title="Archivar">
                                                    <i class="ph-bold ph-archive text-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#F2EBE3]/50 text-[#2F3E5C]/30 mb-3">
                                        <i class="ph-bold ph-folder-open text-3xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-[#2F3E5C]/60">No hay documentos registrados para este adulto mayor.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL DE REGISTRO / EDICIÓN --}}
        <template x-if="modalDocumento">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-[#2F3E5C]/45 p-4 backdrop-blur-sm" x-transition.opacity>
                <div class="relative w-full max-w-md scale-100 rounded-[24px] bg-[#E7DDD2] shadow-2xl overflow-hidden" @click.stop x-transition>
                    <div class="h-1.5 w-full bg-[#2F3E5C]"></div>
                    
                    <div class="px-6 py-5 border-b border-[#CBBBAA]/30 flex justify-between items-center bg-white/50">
                        <h3 class="text-lg font-black text-[#2F3E5C]" x-text="isEditingDoc ? 'Editar Metadatos del Documento' : 'Subir Nuevo Documento'"></h3>
                        <button @click="cerrarModal()" class="text-[#2F3E5C]/50 hover:text-red-500 transition">
                            <i class="ph-bold ph-x text-xl"></i>
                        </button>
                    </div>

                    <div class="p-6 bg-white">
                        <form :action="isEditingDoc ? '{{ url('admin/adultos-mayores/'.$adulto_mayor->cod_am.'/documentos') }}/' + docData.id : '{{ route('admin.adultos-mayores.documentos.store', $adulto_mayor->cod_am) }}'" method="POST" enctype="multipart/form-data" @submit="validarDocumento">
                            @csrf
                            <template x-if="isEditingDoc">
                                <input type="hidden" name="_method" value="PATCH">
                            </template>
                            
                            {{-- ID del adulto para el store --}}
                            <input type="hidden" name="cod_am" value="{{ $adulto_mayor->cod_am }}">

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/60 mb-1.5">Título / Nombre del Documento *</label>
                                    <input type="text" name="nom_doc" x-model="docData.nom_doc" class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-4 py-2.5 text-sm font-bold text-[#2F3E5C] focus:border-[#2F3E5C] focus:ring-0">
                                    <p x-show="errors.nom_doc" x-text="errors.nom_doc" class="mt-1 text-xs font-bold text-red-500"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/60 mb-1.5">Clasificación *</label>
                                    <select name="tipo_doc" x-model="docData.tipo_doc" class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-4 py-2.5 text-sm font-bold text-[#2F3E5C] focus:border-[#2F3E5C] focus:ring-0">
                                        <option value="">Seleccione tipo de documento</option>
                                        <option value="IDENTIDAD">Documento de Identidad (CI, Pasaporte)</option>
                                        <option value="MEDICO">Reporte Médico Ext.</option>
                                        <option value="LABORATORIO">Análisis / Laboratorio</option>
                                        <option value="LEGAL">Documento Legal / Sentencia</option>
                                        <option value="RECETA">Receta Médica Ext.</option>
                                        <option value="OTRO">Otro documento general</option>
                                    </select>
                                    <p x-show="errors.tipo_doc" x-text="errors.tipo_doc" class="mt-1 text-xs font-bold text-red-500"></p>
                                </div>

                                <div x-show="!isEditingDoc">
                                    <label class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/60 mb-1.5">Archivo a subir *</label>
                                    <input type="file" name="archivo" accept=".pdf,image/jpeg,image/png,image/webp" class="block w-full text-sm text-[#2F3E5C]/70 file:mr-4 file:rounded-xl file:border-0 file:bg-[#2F3E5C]/10 file:px-4 file:py-2.5 file:text-xs file:font-black file:text-[#2F3E5C] hover:file:bg-[#2F3E5C]/20 cursor-pointer">
                                    <p class="mt-1 text-xs font-bold text-[#2F3E5C]/40">Formatos permitidos: PDF, JPG, PNG (Max. 5MB)</p>
                                    <p x-show="errors.archivo" x-text="errors.archivo" class="mt-1 text-xs font-bold text-red-500"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/60 mb-1.5">Observaciones adicionales</label>
                                    <textarea name="observaciones" x-model="docData.observaciones" rows="2" class="w-full rounded-xl border border-[#CBBBAA]/60 bg-white px-4 py-2.5 text-sm font-bold text-[#2F3E5C] focus:border-[#2F3E5C] focus:ring-0" placeholder="Opcional..."></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3 border-t border-[#CBBBAA]/30 pt-5">
                                <button type="button" @click="cerrarModal()" class="rounded-xl px-4 py-2.5 text-xs font-black text-[#2F3E5C]/60 hover:bg-[#F2EBE3] transition">Cancelar</button>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black text-white shadow-sm hover:bg-[#1F2E4C] transition" :disabled="cargando">
                                    <span x-show="!cargando" x-text="isEditingDoc ? 'Guardar Cambios' : 'Subir Documento'"></span>
                                    <span x-show="cargando">Procesando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-sistema-layout>
