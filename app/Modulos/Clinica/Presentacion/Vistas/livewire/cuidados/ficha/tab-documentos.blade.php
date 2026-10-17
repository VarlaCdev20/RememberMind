@php
    $documentos = $this->documentosPaginados['items'];
    $paginacion = $this->documentosPaginados;
    $metricas = $this->metricasDocumentos;
    $documentoActivo = $this->documentoActivo;
    $tabDetalleDoc = $this->tabDetalleDoc;
    $zoomDoc = $this->zoomDoc;
@endphp

<div class="space-y-5"
     x-data="{
         tabDocActivo: @entangle('tabDetalleDoc'),
         zoomNivel: @entangle('zoomDoc'),
         modalSubir: @entangle('modalSubirDoc'),
         menuAccionDoc: null
     }">

    {{-- ========================================================================= --}}
    @include('clinica::livewire.cuidados.ficha.documentos.cabecera')
    @include('clinica::livewire.cuidados.ficha.documentos.resumen')
    @include('clinica::livewire.cuidados.ficha.documentos.filtros')
    {{-- 4. ESTRUCTURA PRINCIPAL — DOS COLUMNAS (65% IZQUIERDA / 35% DERECHA)     --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- ===================================================================== --}}
    @include('clinica::livewire.cuidados.ficha.documentos.listado')
    @include('clinica::livewire.cuidados.ficha.documentos.detalle')
    </div>
    {{-- 7. MODAL CENTRAL: + SUBIR DOCUMENTO                                       --}}
    {{-- ========================================================================= --}}
    @if($modalSubirDoc)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Cabecera del modal --}}
                <div class="p-4 sm:p-5 border-b border-[var(--rm-border)] flex items-center justify-between bg-[var(--rm-surface-alt)]">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-[var(--rm-action-primary)]/10 text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/20 flex items-center justify-center shrink-0">
                            <i class="ph-bold ph-upload-simple text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-[var(--rm-text-title)]">
                                Subir documento al expediente
                            </h3>
                            <p class="text-xs text-[var(--rm-text-muted)]">
                                Complete los metadatos y seleccione el archivo digitalizado.
                            </p>
                        </div>
                    </div>
                    <button type="button"
                            wire:click="cerrarModalSubirDoc"
                            class="p-1.5 rounded-xl border border-[var(--rm-border)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition">
                        <i class="ph-bold ph-x text-sm"></i>
                    </button>
                </div>

                {{-- Formulario --}}
                <form wire:submit="guardarNuevoDocumento" class="p-4 sm:p-5 space-y-4 max-h-[80vh] overflow-y-auto">
                    
                    {{-- Dropzone / File input --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1.5">
                            Archivo digital (PDF o Imagen) *
                        </label>
                        <div class="p-4 rounded-2xl border-2 border-dashed border-[var(--rm-border)] hover:border-[var(--rm-action-primary)] bg-[var(--rm-surface-alt)] text-center transition cursor-pointer relative">
                            <input type="file"
                                   wire:model="nuevoDocArchivo"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                            <div class="space-y-1">
                                <i class="ph-bold ph-cloud-arrow-up text-3xl text-[var(--rm-action-primary)] mx-auto block"></i>
                                <p class="text-xs font-bold text-[var(--rm-text-title)]">
                                    Haga clic o arrastre el archivo aquí
                                </p>
                                <p class="text-[11px] text-[var(--rm-text-muted)]">
                                    Formatos permitidos: PDF, JPG, PNG, WebP (Máx. 15 MB)
                                </p>
                            </div>
                        </div>
                        @if($nuevoDocArchivo)
                            <div class="mt-2 p-2 rounded-xl bg-[var(--rm-success-soft)] border border-[var(--rm-success-strong)] text-[var(--rm-success-strong)] text-xs font-bold flex items-center gap-2">
                                <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)]"></i>
                                <span class="truncate">Archivo listo para cargar: {{ $nuevoDocArchivo->getClientOriginalName() }}</span>
                            </div>
                        @endif
                        @error('nuevoDocArchivo')
                            <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nombre del Documento --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                            Nombre del documento *
                        </label>
                        <input type="text"
                               wire:model="nuevoDocNombre"
                               placeholder="Ej. Evaluación médica geriátrica semestral"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
                        @error('nuevoDocNombre')
                            <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Grid Tipo y Categoría --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Tipo de documento *
                            </label>
                            <input type="text"
                                   wire:model="nuevoDocTipo"
                                   placeholder="Ej. Informe, Estudio, Consentimiento"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
                            @error('nuevoDocTipo')
                                <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Categoría *
                            </label>
                            <select wire:model="nuevoDocCategoria"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
                                <option value="CLINICO">Clínico</option>
                                <option value="ADMINISTRATIVO">Administrativo</option>
                                <option value="IMAGEN">Imágenes</option>
                                <option value="LEGAL">Legal</option>
                                <option value="PERSONAL">Personal</option>
                            </select>
                            @error('nuevoDocCategoria')
                                <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Grid Fecha y Módulo relacionado --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Fecha del documento *
                            </label>
                            <input type="date"
                                   wire:model="nuevoDocFecha"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
                            @error('nuevoDocFecha')
                                <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                                Módulo relacionado
                            </label>
                            <input type="text"
                                   wire:model="nuevoDocModulo"
                                   placeholder="Ej. Ficha Médica / Enfermería"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                            Descripción corta
                        </label>
                        <input type="text"
                               wire:model="nuevoDocDescripcion"
                               placeholder="Resumen del contenido del documento"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden">
                    </div>

                    {{-- Observaciones --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-1">
                            Observaciones adicionales
                        </label>
                        <textarea wire:model="nuevoDocObservaciones"
                                  rows="2"
                                  placeholder="Notas sobre validez, autorizaciones o profesional emisor"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)] outline-hidden resize-none"></textarea>
                    </div>

                    {{-- Botones de acción del modal --}}
                    <div class="pt-3 border-t border-[var(--rm-border)] flex items-center justify-end gap-2.5">
                        <button type="button"
                                wire:click="cerrarModalSubirDoc"
                                class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-title)] transition">
                            Cancelar
                        </button>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary)]/90 text-[var(--rm-text-on-primary)] text-xs font-bold transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <span wire:loading.remove wire:target="guardarNuevoDocumento">Registrar documento</span>
                            <span wire:loading wire:target="guardarNuevoDocumento">Subiendo archivo...</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    @endif

</div>
