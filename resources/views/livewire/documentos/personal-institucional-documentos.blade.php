<div class="space-y-6">
    <!-- Formulario para subir nuevo documento -->
    <div class="bg-fondo-hover p-4 rounded-2xl border border-borde">
        <h4 class="text-sm font-bold text-titulo mb-3 flex items-center gap-2">
            <i class="ph-bold ph-upload-simple"></i> Subir Nuevo Documento
        </h4>
        <form wire:submit.prevent="subirDocumento" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="col-span-1">
                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Tipo de Documento</label>
                <select wire:model="cod_tipo_doc" class="w-full rounded-xl border-input-borde bg-white text-sm" required>
                    <option value="">Seleccione...</option>
                    @foreach($tiposDocumento as $tipo)
                        <option value="{{ $tipo->cod_tipo_doc }}">{{ $tipo->nombre_tipo }}</option>
                    @endforeach
                </select>
                @error('cod_tipo_doc') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
            </div>
            <div class="col-span-1 md:col-span-2">
                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Archivo (PDF, IMG)</label>
                <input type="file" wire:model="archivo" class="w-full rounded-xl border border-input-borde bg-white text-sm px-3 py-1.5 focus:outline-none" required>
                @error('archivo') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
            </div>
            <div class="col-span-1 flex gap-2">
                <button type="submit" class="rm-btn-primary w-full h-[38px] flex items-center justify-center gap-2" wire:loading.attr="disabled">
                    <i class="ph-bold ph-paperclip text-lg"></i>
                    <span wire:loading.remove wire:target="subirDocumento">Anexar</span>
                    <span wire:loading wire:target="subirDocumento">Subiendo...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Documentos -->
    <div>
        <h4 class="text-sm font-bold text-titulo mb-3 flex items-center gap-2">
            <i class="ph-bold ph-files"></i> Expediente Documental
        </h4>
        
        @if(count($documentos) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($documentos as $doc)
                <div class="bg-white rounded-2xl border border-borde p-4 flex flex-col gap-3 relative group transition-all hover:shadow-md">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-xl bg-boton-acento/10 text-boton-acento flex items-center justify-center flex-shrink-0">
                                <i class="ph-fill ph-file-pdf text-2xl"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-titulo leading-tight">{{ $doc->tipoDocumento->nombre_tipo ?? 'Documento' }}</h5>
                                <p class="text-[10px] font-semibold text-apoyo uppercase tracking-wider mt-0.5">
                                    {{ \Carbon\Carbon::parse($doc->fecha_subida)->format('d M, Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ Storage::url($doc->ruta_archivo) }}" target="_blank" class="h-7 w-7 rounded border border-borde flex items-center justify-center text-apoyo hover:bg-boton-acento hover:text-white transition-colors" title="Ver archivo">
                                <i class="ph-bold ph-eye"></i>
                            </a>
                            <button wire:click="eliminarDocumento({{ $doc->id_doc_usu }})" class="h-7 w-7 rounded border border-borde flex items-center justify-center text-apoyo hover:bg-estado-peligro hover:text-white transition-colors" title="Eliminar">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-auto border-t border-borde pt-3">
                        @if($doc->estado_validacion === 'VÁLIDO')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-estado-exitoBg text-estado-exito border border-estado-exitoBorde">
                                <i class="ph-fill ph-check-circle mr-1"></i> Válido
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertenciaBorde">
                                <i class="ph-fill ph-warning-circle mr-1"></i> Pendiente
                            </span>
                            @can('usuarios.ver')
                            <button wire:click="validarDocumento({{ $doc->id_doc_usu }})" class="text-xs font-bold text-boton-acento hover:underline">
                                Validar
                            </button>
                            @endcan
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center p-10 text-apoyo border-2 border-dashed border-borde rounded-2xl">
            <i class="ph-fill ph-folder-open text-4xl mb-3 opacity-50"></i>
            <p class="text-sm font-semibold">Este usuario no tiene documentos anexados a su expediente.</p>
        </div>
        @endif
    </div>
</div>
