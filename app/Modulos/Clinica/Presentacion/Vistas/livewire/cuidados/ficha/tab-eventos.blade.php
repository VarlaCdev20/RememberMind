

{{-- ========================================================================= --}}
{{-- PESTAÑA: EVENTOS CLÍNICOS — GOLDEN REFERENCE                             --}}
{{-- Historial de incidentes, caídas, lesiones y eventos clínicos del residente--}}
{{-- ========================================================================= --}}

@php
    $eventos = $this->eventosFiltrados;
    $eventoActivo = $this->eventoActivo;
    $metricas = $this->metricasEventos;
    $porMes = $this->eventosPorMesData;
    $porTipo = $this->eventosPorTipoData;
@endphp

<div class="space-y-6"
     x-data="moduloEventosClinicos({
        mesesLabels: {{ json_encode($porMes['labels']) }},
        mesesData: {{ json_encode($porMes['data']) }},
        tipoLabels: {{ json_encode($porTipo['labels']) }},
        tipoData: {{ json_encode($porTipo['data']) }},
        tipoPercentages: {{ json_encode($porTipo['percentages']) }},
        tipoTotal: {{ $porTipo['total'] }},
        tabInterno: @entangle('tabDetalleEvento')
     })"
     x-init="initModule()">
    {{-- Payload de sincronización reactiva para gráficos --}}
    <div id="eventosChartDataPayload"
         class="hidden"
         data-meses='@json($porMes)'
         data-tipo='@json($porTipo)'></div>

    {{-- ========================================================================= --}}
    @include('clinica::livewire.cuidados.ficha.eventos.cabecera')
    @include('clinica::livewire.cuidados.ficha.eventos.resumen')
    @include('clinica::livewire.cuidados.ficha.eventos.filtros')
    {{-- 4. ÁREA PRINCIPAL — DOS COLUMNAS (LÍNEA DE TIEMPO + DETALLE SELECCIONADO)  --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- ===================================================================== --}}
    @include('clinica::livewire.cuidados.ficha.eventos.cronologia')
    @include('clinica::livewire.cuidados.ficha.eventos.detalle')
    </div>
    {{-- MODAL: REGISTRAR NUEVO EVENTO CLÍNICO                                     --}}
    {{-- ========================================================================= --}}
    @if($modalRegistrarEvento)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-[var(--rm-modal-overlay)] backdrop-blur-xs transition-opacity" wire:click="cerrarModalRegistrarEvento"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)]">
                                <i class="ph-bold ph-first-aid text-xl"></i>
                            </span>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-[var(--rm-text-title)] uppercase tracking-wide">
                                    Registrar Evento Clínico
                                </h3>
                                <p class="text-[11px] text-[var(--rm-text-muted)]">Ficha del Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarModalRegistrarEvento" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] text-lg font-bold cursor-pointer">✕</button>
                    </div>

                    <form wire:submit.prevent="guardarNuevoEvento" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Tipo de Evento *</label>
                                <select wire:model="nuevoEventoTipo" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 font-semibold focus:border-[var(--rm-action-primary)] focus:outline-hidden">
                                    <option value="CAIDA">Caída asistencial</option>
                                    <option value="LESION">Lesión cutánea / herida</option>
                                    <option value="INCIDENTE">Incidente asistencial</option>
                                    <option value="COMPLICACION">Complicación clínica</option>
                                    <option value="OTRO">Otro evento relevante</option>
                                </select>
                                @error('nuevoEventoTipo') <span class="text-[var(--rm-danger-strong)] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Fecha y Hora *</label>
                                <input type="datetime-local" wire:model="nuevoEventoFechaHora" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2 font-mono font-bold focus:border-[var(--rm-action-primary)] focus:outline-hidden">
                                @error('nuevoEventoFechaHora') <span class="text-[var(--rm-danger-strong)] font-bold">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Lugar del Evento *</label>
                                <input type="text" wire:model="nuevoEventoLugar" placeholder="Ej: Pasillo · 2° piso / Baño..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 font-medium focus:border-[var(--rm-action-primary)] focus:outline-hidden">
                                @error('nuevoEventoLugar') <span class="text-[var(--rm-danger-strong)] font-bold">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Nivel de Severidad</label>
                                <select wire:model="nuevoEventoSeveridad" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 font-semibold focus:border-[var(--rm-action-primary)] focus:outline-hidden">
                                    <option value="LEVE">Leve (sin repercusión funcional)</option>
                                    <option value="MODERADA">Moderada (requiere vigilancia)</option>
                                    <option value="GRAVE">Grave / Crítica (requiere médico)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-[var(--rm-text-title)] mb-1">Descripción Clínica Completa *</label>
                            <textarea wire:model="nuevoEventoDescripcion" rows="3" placeholder="Describa el hecho, síntomas del residente, entorno y acciones iniciales..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 focus:border-[var(--rm-action-primary)] focus:outline-hidden"></textarea>
                            @error('nuevoEventoDescripcion') <span class="text-[var(--rm-danger-strong)] font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[var(--rm-text-title)] mb-1">Dolor EVA (0-10)</label>
                                <input type="number" min="0" max="10" wire:model="nuevoEventoDolor" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2 font-mono font-bold">
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" id="chkLesion" wire:model="nuevoEventoLesion" class="rounded border-[var(--rm-border)] text-[var(--rm-action-primary)] focus:ring-[var(--rm-action-primary)]">
                                <label for="chkLesion" class="font-bold cursor-pointer">¿Presenta lesión física?</label>
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="checkbox" id="chkPresenciado" wire:model="nuevoEventoPresenciado" class="rounded border-[var(--rm-border)] text-[var(--rm-action-primary)] focus:ring-[var(--rm-action-primary)]">
                                <label for="chkPresenciado" class="font-bold cursor-pointer">¿Fue presenciado?</label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2.5 pt-3 border-t border-[var(--rm-border)]">
                            <button type="button" wire:click="cerrarModalRegistrarEvento" class="px-4 py-2 rounded-xl border border-[var(--rm-border)] font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition cursor-pointer">
                                Cancelar
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-[var(--rm-text-on-primary)] font-extrabold transition cursor-pointer shadow-sm">
                                Guardar Evento Clínico
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
