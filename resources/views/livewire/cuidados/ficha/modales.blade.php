{{-- ========================================================================= --}}
{{-- 0. MODAL CENTRAL: REGISTRAR ATENCIÓN CLÍNICA (GOLDEN REFERENCE)           --}}
{{-- ========================================================================= --}}
<div x-show="modalSelectorAtencion"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title-atencion"
     role="dialog"
     aria-modal="true">
    
    {{-- Backdrop nítido con opacidad suave 35-45% (sin blur borroso sobre el modal) --}}
    <div x-show="modalSelectorAtencion"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="modalSelectorAtencion = false"
         class="fixed inset-0 bg-slate-900/40 transition-opacity"></div>

    {{-- Contenedor para centrado vertical y horizontal perfecto --}}
    <div class="flex min-h-full items-center justify-center p-3 sm:p-5 text-center">
        <div x-show="modalSelectorAtencion"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.stop
             class="relative w-full max-w-[800px] max-h-[85vh] flex flex-col rounded-[20px] border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xl overflow-hidden text-left font-sans">
            
            {{-- Header del Modal --}}
            <div class="p-5 sm:p-6 border-b border-[var(--rm-border)] flex items-center justify-between gap-4 bg-[var(--rm-surface)]">
                <div class="flex items-center gap-3.5">
                    <div class="h-11 w-11 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i class="ph-bold ph-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 id="modal-title-atencion" class="text-base sm:text-lg font-black tracking-tight text-[var(--rm-text-title)]">
                            Registrar atención clínica
                        </h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                            Selecciona el tipo de atención que deseas registrar.
                        </p>
                    </div>
                </div>

                {{-- Botón Cuadrado de Cerrar X --}}
                <button type="button"
                        @click="modalSelectorAtencion = false"
                        class="h-9 w-9 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] flex items-center justify-center text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer shrink-0"
                        title="Cerrar modal (Esc)">
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            {{-- Body Scrollable --}}
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1">
                
                {{-- 5. Contexto del Residente (Card Horizontal Compacta Obligatoria) --}}
                <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Foto / Avatar --}}
                        @if($adultoMayor->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($adultoMayor->foto))
                            <img src="{{ asset('storage/' . $adultoMayor->foto) }}"
                                 alt="{{ $adultoMayor->nombres }}"
                                 class="h-12 w-12 rounded-xl object-cover border border-[var(--rm-border)] shadow-2xs shrink-0">
                        @else
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-[#1E3A8A] font-black text-base shrink-0 border border-blue-200">
                                {{ strtoupper(substr($adultoMayor->nombres, 0, 1) . substr($adultoMayor->ap_paterno, 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <h4 class="text-sm font-black text-[var(--rm-text-title)] truncate">
                                {{ $adultoMayor->nombre_completo }}
                            </h4>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-[var(--rm-text-muted)] font-medium">
                                <span>{{ $adultoMayor->edad_texto ?? ($adultoMayor->edad . ' años') }}</span>
                                <span>·</span>
                                <span class="font-mono font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->cod_am }}</span>
                                <span>·</span>
                                <span>{{ $adultoMayor->habitacion_texto ?? 'HAB-D01' }}</span>
                                <span>·</span>
                                <span>{{ $adultoMayor->cama_texto ?? 'CAM-D01-01' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Badges a la derecha --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800 border border-amber-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                            <span>{{ $adultoMayor->estado_humano ?: 'Vigilancia' }}</span>
                        </span>

                        @php $alertasActCount = $adultoMayor->alertas ? $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count() : 2; @endphp
                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                            <i class="ph-bold ph-warning"></i>
                            <span>{{ $alertasActCount > 0 ? $alertasActCount . ' alertas activas' : 'Sin alertas activas' }}</span>
                        </span>
                    </div>
                </div>

                {{-- 6. Cuadrícula Principal (2 Columnas x 4 Filas = 8 Opciones Clínicas) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                    
                    {{-- OPCIÓN 1 — SIGNOS VITALES --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; $wire.abrirModalSignos()"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-heartbeat text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Signos vitales
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Registrar signos y control fisiológico.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    PA, FC, FR, SpO₂, Temperatura, Dolor, etc.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 2 — CUIDADO DE ENFERMERÍA --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-blue-50 border border-blue-200 text-[#1E3A8A] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-hand-heart text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Cuidado de enfermería
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Registrar cuidado asistencial realizado.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    Higiene, alimentación, hidratación, movilidad, eliminación, piel, etc.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 3 — MEDICACIÓN --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; activeTab = 'medicacion'; $wire.cambiarTab('medicacion'); $wire.abrirModalMedicacion()"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-pill text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Medicación
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Administrar medicación prescrita.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    Dosis programadas, PRN, registro de administración.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 4 — EVOLUCIÓN DE ENFERMERÍA --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; activeTab = 'seguimiento'; $wire.cambiarTab('seguimiento'); $wire.abrirModalSeguimiento()"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-article text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Evolución de enfermería
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Registrar evolución y estado actual.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    Estado general, cambios observados, intervención, seguimiento.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 5 — SEGUIMIENTO DE GUARDIA --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; $wire.abrirModalSeguimiento()"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-sky-50 border border-sky-200 text-sky-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-clipboard-text text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Seguimiento de guardia
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Registrar seguimiento durante el turno.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    Observación, reevaluación, continuidad de cuidados.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 6 — INCIDENTE / CAÍDA --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; $wire.abrirModalIncidente()"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-rose-50/30 hover:border-rose-300 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-warning-octagon text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-rose-900 group-hover:text-rose-700 transition-colors">
                                    Incidente / Caída
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Reportar incidente o evento asistencial.
                                </p>
                                <p class="text-[10.5px] text-rose-700/80 mt-0.5 truncate">
                                    Caídas, lesiones, eventos adversos, acciones realizadas.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-rose-700 group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 7 — DOLOR / SÍNTOMA --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; $wire.abrirModalSignos()"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-smiley-sad text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Dolor / Síntoma
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Registrar síntoma o cambio clínico.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    Dolor EVA, localización, intensidad, intervención.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                    {{-- OPCIÓN 8 — PROCEDIMIENTO / DISPOSITIVO --}}
                    <button type="button"
                            @click="modalSelectorAtencion = false; activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                            class="group p-3.5 sm:p-4 rounded-[16px] border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] hover:border-blue-400/80 shadow-2xs hover:shadow-md transition-all duration-150 flex items-center justify-between gap-3 text-left cursor-pointer min-h-[95px]">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-2xl bg-teal-50 border border-teal-200 text-teal-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <i class="ph-bold ph-bandaids text-2xl"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-black text-[var(--rm-text-title)] group-hover:text-[#1E3A8A] transition-colors">
                                    Procedimiento / Dispositivo
                                </h4>
                                <p class="text-xs font-medium text-[var(--rm-text-body)] mt-0.5">
                                    Registrar procedimiento o control de dispositivo.
                                </p>
                                <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5 truncate">
                                    Curaciones, sondas, catéteres, oxígeno, etc.
                                </p>
                            </div>
                        </div>
                        <i class="ph-bold ph-caret-right text-base text-[var(--rm-text-muted)] group-hover:text-[#1E3A8A] group-hover:translate-x-0.5 transition-all shrink-0"></i>
                    </button>

                </div>

            </div>

            {{-- Footer del Modal --}}
            <div class="p-4 sm:p-5 border-t border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-start gap-2.5 text-xs text-[var(--rm-text-muted)] leading-relaxed">
                    <i class="ph-bold ph-info text-[#1E3A8A] text-base shrink-0 mt-0.5"></i>
                    <span>Toda la información se registra en el historial clínico del residente, con trazabilidad y fecha/hora automática.</span>
                </div>
                <button type="button"
                        @click="modalSelectorAtencion = false"
                        class="px-5 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] transition cursor-pointer self-end sm:self-auto shrink-0 shadow-2xs">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
</div>


{{-- MODALES CLÍNICOS OPERATIVOS (CENTRALIZADOS EN LA FICHA MÉDICA) --}}

{{-- 1. MODAL REGISTRO DE SIGNOS VITALES --}}
@if($modalSignos)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-panel">
            <h3 class="text-base font-bold text-[var(--rm-text-title)]">Registrar Control de Signos Vitales</h3>
            <p class="text-xs text-[var(--rm-text-muted)] mt-1">Parámetros fisiológicos y hemodinámicos del residente.</p>

            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Presión Arterial (PA) *</label>
                    <input type="text" wire:model="signoPA" placeholder="120/80" class="rm-input w-full text-xs" />
                    @error('signoPA') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Frecuencia Cardaca (FC)</label>
                    <input type="number" wire:model="signoFC" placeholder="75" class="rm-input w-full text-xs" />
                    @error('signoFC') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Frecuencia Resp. (FR)</label>
                    <input type="number" wire:model="signoFR" placeholder="18" class="rm-input w-full text-xs" />
                    @error('signoFR') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Temperatura (°C)</label>
                    <input type="number" step="0.1" wire:model="signoTemp" placeholder="36.5" class="rm-input w-full text-xs" />
                    @error('signoTemp') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Saturación SpO2 (%)</label>
                    <input type="number" wire:model="signoSat" placeholder="98" class="rm-input w-full text-xs" />
                    @error('signoSat') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Glucosa (mg/dL)</label>
                    <input type="number" wire:model="signoGlucosa" placeholder="105" class="rm-input w-full text-xs" />
                    @error('signoGlucosa') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Nivel de Dolor (Escala EVA 0 a 10)</label>
                    <div class="flex items-center gap-3">
                        <input type="range" min="0" max="10" wire:model.live="signoDolor" class="w-full accent-blue-600" />
                        <span class="font-black text-sm px-2.5 py-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] min-w-[3rem] text-center">
                            {{ $signoDolor ?? 0 }}/10
                        </span>
                    </div>
                </div>
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Posición durante el control</label>
                    <select wire:model="signoPosicion" class="rm-input w-full text-xs"><option value="">No registrada</option><option>SENTADO</option><option>ACOSTADO</option><option>DE_PIE</option></select>
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input id="signo-oxigeno" type="checkbox" wire:model="signoUsaOxigeno" class="rounded border-[var(--rm-border)] text-boton-principal">
                    <label for="signo-oxigeno" class="font-bold text-[var(--rm-text-body)]">Usa oxígeno</label>
                </div>
                @error('signoPA')
                    <label class="col-span-2 flex items-start gap-2 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 text-xs font-bold text-[var(--rm-text-title)]">
                        <input type="checkbox" wire:model="signoConfirmarAtipico" class="mt-0.5 rounded border-[var(--rm-border)] text-boton-principal">
                        Confirmo que repetí la medición y deseo conservar este valor atípico.
                    </label>
                @enderror
                <div class="col-span-2">
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Observaciones</label>
                    <textarea wire:model="signoObs" rows="2" placeholder="Notas clínicas adicionales..." class="rm-input w-full text-xs"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalSignos" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarSignos" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    <span wire:loading.remove wire:target="guardarSignos">Guardar signos</span>
                    <span wire:loading wire:target="guardarSignos">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 2. MODAL ADMINISTRACIÓN DE MEDICACIÓN --}}
@if($modalMed)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-panel">
            <h3 class="text-base font-bold text-[var(--rm-text-title)]">Administración de Medicación</h3>
            <div class="mt-2 rounded-xl bg-[var(--rm-surface-alt)] p-3 border border-[var(--rm-border)] text-xs space-y-1">
                <p class="font-bold text-[var(--rm-text-title)] text-sm">{{ $medNombre }}</p>
                <p class="text-[var(--rm-text-body)]">Dosis: <strong>{{ $medDosis }}</strong> · Vía: <strong>{{ $medVia }}</strong></p>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Acción Asistencial *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="$set('medAccion', 'ADMINISTRAR')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'ADMINISTRAR' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-check mr-1"></i> Administrada
                        </button>
                        <button type="button"
                                wire:click="$set('medAccion', 'OMITIR')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'OMITIR' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-warning mr-1"></i> Omitida
                        </button>
                        <button type="button" wire:click="$set('medAccion', 'RECHAZAR')" class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'RECHAZAR' ? 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}"><i class="ph-bold ph-hand mr-1"></i> Rechazada</button>
                        <button type="button" wire:click="$set('medAccion', 'NO_DISPONIBLE')" class="rounded-xl py-2 font-bold text-center border transition {{ $medAccion === 'NO_DISPONIBLE' ? 'bg-estado-peligroBg text-estado-peligro border-estado-peligroBorde' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}"><i class="ph-bold ph-package mr-1"></i> No disponible</button>
                    </div>
                </div>

                @if($medAccion !== 'ADMINISTRAR')
                    <div>
                        <label class="font-bold text-rose-700 block mb-1">Motivo de Omisión *</label>
                        <textarea wire:model="medMotivoOmision" rows="2" placeholder="Rechazo del paciente, náuseas, etc." class="rm-input w-full text-xs"></textarea>
                        @error('medMotivoOmision') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($medEsPrn && $medAccion === 'ADMINISTRAR')
                    <div class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3 space-y-3">
                        <p class="text-xs font-black text-[var(--rm-text-title)]">PRN · {{ $medCondicionPrn }}</p>
                        <label class="block font-bold text-[var(--rm-text-body)]">Síntoma o motivo actual *<textarea wire:model="medMotivoPrn" rows="2" class="rm-input mt-1 w-full text-xs"></textarea>@error('medMotivoPrn')<span class="text-estado-peligro text-[10px] font-bold">{{ $message }}</span>@enderror</label>
                        <label class="block font-bold text-[var(--rm-text-body)]">Valoración previa *<textarea wire:model="medValoracionPrevia" rows="2" class="rm-input mt-1 w-full text-xs"></textarea>@error('medValoracionPrevia')<span class="text-estado-peligro text-[10px] font-bold">{{ $message }}</span>@enderror</label>
                        <label class="block font-bold text-[var(--rm-text-body)]">Intensidad 0–10<input wire:model="medIntensidadPrevia" type="number" min="0" max="10" class="rm-input mt-1 w-full text-xs"></label>
                        <p class="text-[10px] font-bold text-[var(--rm-text-muted)]">Se programará una reevaluación una hora después.</p>
                    </div>
                @endif

                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Observaciones / Efectos Observados</label>
                    <textarea wire:model="medEfectoObs" rows="2" placeholder="Tolerancia adecuada..." class="rm-input w-full text-xs"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalMedicacion" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarMedicacion" wire:loading.attr="disabled" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                    <span wire:loading.remove wire:target="guardarMedicacion">Confirmar registro</span>
                    <span wire:loading wire:target="guardarMedicacion">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 3. MODAL REGISTRAR EJECUCIÓN DE TAREA DE CUIDADO --}}
@if($modalTarea)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-panel">
            <h3 class="text-base font-bold text-[var(--rm-text-title)]">Registrar Actividad de Cuidado</h3>
            <p class="text-xs text-[var(--rm-text-body)] font-bold mt-1.5">{{ $tareaTitulo }}</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Estado de Ejecución *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="$set('tareaEstadoAccion', 'REALIZADA')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $tareaEstadoAccion === 'REALIZADA' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-check-circle mr-1"></i> Realizada
                        </button>
                        <button type="button"
                                wire:click="$set('tareaEstadoAccion', 'OMITIDA')"
                                class="rounded-xl py-2 font-bold text-center border transition {{ $tareaEstadoAccion === 'OMITIDA' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] border-[var(--rm-border)]' }}">
                            <i class="ph-bold ph-prohibit mr-1"></i> Omitida
                        </button>
                    </div>
                </div>

                @if($tareaEstadoAccion === 'REALIZADA')
                    <div>
                        <label class="font-bold text-[var(--rm-text-body)] block mb-1">Resultado / Observación de la Tarea</label>
                        <textarea wire:model="tareaResultado" rows="2" placeholder="Ej: Realizado sin inconvenientes, residente colaborador..." class="rm-input w-full text-xs"></textarea>
                    </div>
                @else
                    <div>
                        <label class="font-bold text-rose-700 block mb-1">Motivo de Omisión *</label>
                        <textarea wire:model="tareaMotivoOmision" rows="2" placeholder="Motivo de no realización..." class="rm-input w-full text-xs"></textarea>
                        @error('tareaMotivoOmision') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>
                @endif
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalTarea" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarTarea" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    <span wire:loading.remove wire:target="guardarTarea">Guardar registro</span>
                    <span wire:loading wire:target="guardarTarea">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 4. MODAL REGISTRAR EVOLUCIÓN CLÍNICA --}}
@if($modalSeguimiento)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div class="w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-xl space-y-4">
            
            {{-- Cabecera del Modal --}}
            <div class="flex items-start justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[#1E3A8A] border border-[var(--rm-border)] shadow-xs">
                        <i class="ph-bold ph-clipboard-text text-xl"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-[var(--rm-text-title)]">Registrar Evolución Clínica</h3>
                        <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Expediente evolutivo asistencial del residente</p>
                    </div>
                </div>
                <button type="button" wire:click="cerrarModalSeguimiento" class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            {{-- Bloque de Trazabilidad Automática --}}
            <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-user-circle text-sm text-[#1E3A8A]"></i>
                    <span>Profesional: <strong class="text-[var(--rm-text-title)]">{{ auth()->user()?->name ?? 'Equipo Asistencial' }}</strong></span>
                    <span>·</span>
                    <span class="text-[10px] font-bold uppercase bg-[var(--rm-surface)] px-2 py-0.5 rounded border border-[var(--rm-border)] text-[var(--rm-text-muted)]">{{ auth()->user()?->roles->first()?->name ?? 'Enfermería' }}</span>
                </div>
                <div class="flex items-center gap-1.5 font-mono text-[11px] text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-calendar"></i>
                    <span>{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Formulario Clínico --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                {{-- Estado General --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Estado General *</label>
                    <select wire:model="segEstado" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[#1E3A8A]">
                        <option value="ESTABLE">Estable</option>
                        <option value="VIGILANCIA">En Vigilancia</option>
                        <option value="DELICADO">Delicado</option>
                        <option value="CRITICO">Crítico</option>
                    </select>
                </div>

                {{-- Alimentación / Ingesta --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Alimentación / Ingesta</label>
                    <select wire:model="segAlimentacion" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[#1E3A8A]">
                        <option value="COMPLETA">Completa / Buena ingesta</option>
                        <option value="PARCIAL">Parcial / Regular</option>
                        <option value="RECHAZADA">Rechazada</option>
                        <option value="AYUNO">Ayuno indicado</option>
                    </select>
                </div>

                {{-- Movilidad Funcional --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Movilidad Funcional</label>
                    <select wire:model="segMovilidad" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[#1E3A8A]">
                        <option value="INDEPENDIENTE">Independiente / Autónoma</option>
                        <option value="ASISTIDA">Asistida con apoyo</option>
                        <option value="SILLA_RUEDAS">Silla de ruedas</option>
                        <option value="ENCAMADO">Encamado</option>
                    </select>
                </div>

                {{-- Patrón de Descanso / Sueño --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Patrón de Descanso / Sueño</label>
                    <select wire:model="segSueno" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[#1E3A8A]">
                        <option value="NORMAL">Normal / Reparador</option>
                        <option value="INTERRUMPIDO">Interrumpido / Inquieto</option>
                        <option value="INSOMNIO">Insomnio persistente</option>
                        <option value="SOMNOLENCIA">Somnolencia / Sedación</option>
                    </select>
                </div>

                {{-- Opciones de Alerta / Seguimiento --}}
                <div class="col-span-1 sm:col-span-2 p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-2">
                    <label class="flex items-center gap-2 font-bold text-amber-800 dark:text-amber-300 cursor-pointer text-xs">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded border-[var(--rm-border)] text-amber-600 focus:ring-amber-500" />
                        <span>Requiere seguimiento o valoración médica complementaria</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400 cursor-pointer text-xs">
                        <input type="checkbox" wire:model="segIncidente" class="rounded border-[var(--rm-border)] text-rose-600 focus:ring-rose-500" />
                        <span>Ocurrió un incidente clínico en el turno</span>
                    </label>
                </div>

                {{-- Observación / Nota de Evolución Clínica --}}
                <div class="col-span-1 sm:col-span-2">
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Evolución Clínica y Observaciones Asistenciales *</label>
                    <textarea wire:model="segObs" rows="3" placeholder="Detalle de cambios observados, respuesta al tratamiento, estado de ánimo y acciones realizadas..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 text-xs text-[var(--rm-text-title)] focus:ring-1 focus:ring-[#1E3A8A]"></textarea>
                    @error('segObs') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Pie del Modal con Botón Azul Oscuro --}}
            <div class="flex items-center justify-between border-t border-[var(--rm-border)] pt-3 text-xs">
                <span class="text-[10.5px] text-[var(--rm-text-muted)] flex items-center gap-1">
                    <i class="ph-bold ph-lock"></i>
                    <span>Registro firmado e inmutable</span>
                </span>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="cerrarModalSeguimiento" class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardarSeguimiento" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] text-xs font-bold text-white shadow-xs transition inline-flex items-center gap-1.5">
                        <i class="ph-bold ph-check" wire:loading.remove wire:target="guardarSeguimiento"></i>
                        <span wire:loading.remove wire:target="guardarSeguimiento">Registrar evolución</span>
                        <span wire:loading wire:target="guardarSeguimiento">Guardando...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
@endif

{{-- 5. MODAL REPORTAR INCIDENTE --}}
@if($modalIncidente)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-panel">
            <h3 class="text-base font-bold text-rose-700 flex items-center gap-2">
                <i class="ph-bold ph-warning-octagon text-lg"></i>
                <span>Reportar Incidente Asistencial</span>
            </h3>
            <p class="text-xs text-[var(--rm-text-muted)] mt-1">Generación de alerta clínica inmediata para el equipo.</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Tipo de Incidente *</label>
                    <select wire:model="incidenteTipo" class="rm-input w-full text-xs">
                        <option value="INCIDENTE">Incidente General</option>
                        <option value="CAIDA">Caída o Tropiezo</option>
                        <option value="CONDUCTA">Agitación / Desorientación</option>
                        <option value="DOLOR_AGUDO">Dolor Agudo / Malestar Súbito</option>
                        <option value="DESVIACION_CLINICA">Desviación Clínica / Síntoma Inesperado</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Nivel de Severidad *</label>
                    <select wire:model="incidenteNivel" class="rm-input w-full text-xs">
                        <option value="ALTO">Alto (Atención Inmediata)</option>
                        <option value="MEDIO">Medio (Vigilancia Estricta)</option>
                        <option value="BAJO">Bajo (Reporte de Rutina)</option>
                    </select>
                </div>

                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Descripción de lo Sucedido *</label>
                    <textarea wire:model="incidenteMotivo" rows="3" placeholder="Circunstancias, estado físico y medidas inmediatas adoptadas..." class="rm-input w-full text-xs"></textarea>
                    @error('incidenteMotivo') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalIncidente" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarIncidente" wire:loading.attr="disabled" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-700 transition">
                    <span wire:loading.remove wire:target="guardarIncidente">Emitir reporte de alerta</span>
                    <span wire:loading wire:target="guardarIncidente">Emitiendo...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 6. MODAL ATENDER ALERTA --}}
@if($modalAtenderAlerta)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-panel">
            <h3 class="text-base font-bold text-[var(--rm-text-title)]">Atender Alerta Clínica</h3>
            <p class="text-xs text-[var(--rm-text-muted)] mt-1">Registrar primera intervención y pasar alerta a estado EN ATENCIÓN.</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Acción Inmediata Realizada *</label>
                    <textarea wire:model="accionTomadaAlerta" rows="3" placeholder="Ej: Se acomodó en cama, se verificó vía aérea, se administró medicación indicada..." class="rm-input w-full text-xs"></textarea>
                    @error('accionTomadaAlerta') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalAtenderAlerta" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarAtenderAlerta" wire:loading.attr="disabled" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 transition">
                    <span wire:loading.remove wire:target="guardarAtenderAlerta">Registrar atención</span>
                    <span wire:loading wire:target="guardarAtenderAlerta">Guardando...</span>
                </button>
            </div>
        </div>
    </div>
@endif

{{-- 7. MODAL CERRAR ALERTA --}}
@if($modalCerrarAlerta)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-panel">
            <h3 class="text-base font-bold text-[var(--rm-text-title)]">Cerrar Alerta Clínica</h3>
            <p class="text-xs text-[var(--rm-text-muted)] mt-1">Confirme la estabilización del residente y el cierre del evento.</p>

            <div class="mt-4 space-y-3 text-xs">
                <div>
                    <label class="font-bold text-[var(--rm-text-body)] block mb-1">Observación de Cierre / Resolución *</label>
                    <textarea wire:model="observacionCierreAlerta" rows="3" placeholder="Parámetros normalizados, residente estable..." class="rm-input w-full text-xs"></textarea>
                    @error('observacionCierreAlerta') <span class="text-rose-600 text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalCerrarAlerta" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarCerrarAlerta" wire:loading.attr="disabled" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700 transition">
                    <span wire:loading.remove wire:target="guardarCerrarAlerta">Cerrar alerta</span>
                    <span wire:loading wire:target="guardarCerrarAlerta">Cerrando...</span>
                </button>
            </div>
        </div>
    </div>
@endif


{{-- ========================================================================= --}}
{{-- 8. MODAL FLOTANTE: EXPORTAR FICHA E INFORMES (DESIGN SYSTEM)             --}}
{{-- ========================================================================= --}}
<div x-show="modalExportar"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 font-sans"
     role="dialog"
     aria-modal="true">
    
    <div class="w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-5"
         @click.outside="modalExportar = false">
        
        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-[var(--rm-border)] pb-4">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-[#1E3A8A] border border-blue-200">
                    <i class="ph-bold ph-download-simple text-2xl"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-[var(--rm-text-title)]">Exportar Ficha e Informes</h3>
                    <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} · CI: {{ $adultoMayor->ci }}</p>
                </div>
            </div>
            <button type="button"
                    @click="modalExportar = false"
                    class="p-2 rounded-xl text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        {{-- Grid de Opciones de Exportación --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            {{-- Opción 1: Ficha Médica Completa --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-blue-300 hover:bg-blue-50/20 transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-blue-100 text-[#1E3A8A] shrink-0">
                        <i class="ph-bold ph-file-pdf text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Ficha Médica Integral</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Informe clínico consolidado con antecedentes, signos y medicación.</p>
                    </div>
                </div>
                <button type="button"
                        onclick="window.print()"
                        class="w-full py-2 px-3 rounded-xl bg-[#1E3A8A] text-white font-bold text-xs hover:bg-blue-900 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-printer"></i>
                    <span>Imprimir / Guardar PDF</span>
                </button>
            </div>

            {{-- Opción 2: Signos Vitales CSV --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-emerald-300 hover:bg-emerald-50/20 transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800 shrink-0">
                        <i class="ph-bold ph-file-csv text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Historial de Signos (CSV)</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Planilla estructurada con tomas de PA, FC, FR, SpO2, T° y Glucemia.</p>
                    </div>
                </div>
                <button type="button"
                        @click="modalExportar = false; activeTab = 'signos'; $wire.cambiarTab('signos')"
                        class="w-full py-2 px-3 rounded-xl bg-emerald-700 text-white font-bold text-xs hover:bg-emerald-800 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-table"></i>
                    <span>Ver y Exportar en Signos</span>
                </button>
            </div>

            {{-- Opción 3: Plan Farmacológico --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-amber-300 hover:bg-amber-50/20 transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-amber-100 text-amber-800 shrink-0">
                        <i class="ph-bold ph-pill text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Cronograma Farmacológico</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Receta y tratamientos activos, dosis programadas y adherencia.</p>
                    </div>
                </div>
                <button type="button"
                        @click="modalExportar = false; activeTab = 'medicacion'; $wire.cambiarTab('medicacion')"
                        class="w-full py-2 px-3 rounded-xl bg-amber-700 text-white font-bold text-xs hover:bg-amber-800 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-eye"></i>
                    <span>Consultar en Medicación</span>
                </button>
            </div>

            {{-- Opción 4: Documentos y Estudios --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-indigo-300 hover:bg-indigo-50/20 transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-indigo-100 text-indigo-800 shrink-0">
                        <i class="ph-bold ph-folder-open text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Expediente Digital</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Laboratorios, estudios de imagenología y documentación de ingreso.</p>
                    </div>
                </div>
                <button type="button"
                        @click="modalExportar = false; activeTab = 'documentos'; $wire.cambiarTab('documentos')"
                        class="w-full py-2 px-3 rounded-xl bg-indigo-700 text-white font-bold text-xs hover:bg-indigo-800 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-folder"></i>
                    <span>Ir a Documentación</span>
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-[var(--rm-border)] pt-3 text-xs">
            <span class="text-[11px] text-[var(--rm-text-muted)] flex items-center gap-1">
                <i class="ph-bold ph-shield-check text-emerald-600 text-sm"></i>
                Documento trazable y validado por auditoría clínica.
            </span>
            <button type="button"
                    @click="modalExportar = false"
                    class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>
</div>


{{-- ========================================================================= --}}
{{-- 9. VENTANA LATERAL FLOTANTE: EXPEDIENTE CLÍNICO INTEGRAL (SLIDE-OVER DRAWER) --}}
{{-- ========================================================================= --}}
<div x-show="drawerExpediente"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="drawerExpediente = false">
    
    {{-- Backdrop --}}
    <div class="rm-drawer-backdrop"
         x-show="drawerExpediente"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="drawerExpediente = false"></div>

    {{-- Panel Deslizante Lateral --}}
    <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
        <aside x-show="drawerExpediente"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="pointer-events-auto rm-drawer flex h-full w-screen max-w-[620px] flex-col bg-[var(--rm-surface)] shadow-2xl border-l border-[var(--rm-border)] overflow-hidden">
            
            {{-- Header Fijo --}}
            <header class="rm-drawer-header p-5 border-b border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-start justify-between gap-3 shrink-0">
                <div class="space-y-1">
                    <span class="rm-drawer-badge">
                        <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                        EXPEDIENTE CLÍNICO INTEGRAL
                    </span>
                    <div class="flex items-center gap-2 pt-0.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-[#1E3A8A] border border-blue-200 text-base shadow-2xs">
                            <i class="ph-bold ph-identification-card"></i>
                        </span>
                        <h2 class="text-lg font-black text-[var(--rm-text-title)]">{{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }}</h2>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)]">Código: {{ $adultoMayor->cod_am }} · CI: {{ $adultoMayor->ci }}</p>
                </div>
                <button type="button"
                        @click="drawerExpediente = false"
                        class="p-2 rounded-xl text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            {{-- Body Scrollable --}}
            <div class="rm-drawer-body flex-1 overflow-y-auto p-5 space-y-4 text-xs">
                {{-- Bloque Ubicación Habitacional --}}
                <div class="p-4 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-900/60 space-y-2">
                    <span class="text-[10px] font-black uppercase text-amber-800 dark:text-amber-300 tracking-wider block">Ubicación y Censo Activo</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Habitación</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->habitacion?->nombre ?? 'Sin asignar' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Cama</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->cama?->numero ?? 'Sin asignar' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Estado Residente</span>
                            <span class="font-bold text-emerald-700">{{ $adultoMayor->estado?->estado ?? 'ACTIVO' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Datos Demográficos y Civiles --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-3">
                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] tracking-wider block">Filiación y Datos Personales</span>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Fecha de Nacimiento</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->fecha_nacimiento ? \Carbon\Carbon::parse($adultoMayor->fecha_nacimiento)->format('d/m/Y') : 'No registrado' }} ({{ $adultoMayor->edad }} años)</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Género</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->genero ?? 'No especificado' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Lugar de Nacimiento</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->lugar_nacimiento ?? 'Bolivia' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Estado Civil</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->estado_civil ?? 'No declarado' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Ocupación Anterior</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->ocupacion_anterior ?? 'Jubilado/a' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Fecha de Ingreso</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->created_at ? $adultoMayor->created_at->format('d/m/Y') : '10/01/2026' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Datos de Salud y Cobertura --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-3">
                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] tracking-wider block">Perfil de Salud de Base</span>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Grupo Sanguíneo</span>
                            <span class="font-bold text-rose-700">{{ $adultoMayor->grupo_sanguineo ? $adultoMayor->grupo_sanguineo . ($adultoMayor->factor_rh ?: '+') : 'No definido' }}</span>
                        </div>
                        <div>
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Seguro de Salud</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->seguro_salud ?: 'Particular / Sin cobertura' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Alergias Conocidas</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->alergias ?: 'Sin alergias medicamentosas o alimentarias reportadas.' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Dieta y Restricciones</span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $adultoMayor->tipo_dieta ?: 'Dieta normal blanda para adulto mayor, hidratación monitorizada.' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Fijo --}}
            <footer class="p-4 border-t border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-center justify-between gap-3 shrink-0 text-xs">
                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Ficha institucional RememberMind</span>
                <button type="button"
                        @click="drawerExpediente = false"
                        class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition cursor-pointer">
                    Cerrar Panel
                </button>
            </footer>
        </aside>
    </div>
</div>


{{-- ========================================================================= --}}
{{-- 10. VENTANA LATERAL FLOTANTE: RED DE APOYO FAMILIAR (SLIDE-OVER DRAWER)    --}}
{{-- ========================================================================= --}}
<div x-show="drawerFamilia"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="drawerFamilia = false">
    
    {{-- Backdrop --}}
    <div class="rm-drawer-backdrop"
         x-show="drawerFamilia"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="drawerFamilia = false"></div>

    {{-- Panel Deslizante Lateral --}}
    <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
        <aside x-show="drawerFamilia"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="pointer-events-auto rm-drawer flex h-full w-screen max-w-[620px] flex-col bg-[var(--rm-surface)] shadow-2xl border-l border-[var(--rm-border)] overflow-hidden">
            
            {{-- Header Fijo --}}
            <header class="rm-drawer-header p-5 border-b border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-start justify-between gap-3 shrink-0">
                <div class="space-y-1">
                    <span class="rm-drawer-badge">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        RED DE APOYO Y CONTACTOS FAMILIARES
                    </span>
                    <div class="flex items-center gap-2 pt-0.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-base shadow-2xs">
                            <i class="ph-bold ph-users-three"></i>
                        </span>
                        <h2 class="text-lg font-black text-[var(--rm-text-title)]">Contactos Familiares</h2>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)]">Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }}</p>
                </div>
                <button type="button"
                        @click="drawerFamilia = false"
                        class="p-2 rounded-xl text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            {{-- Body Scrollable --}}
            <div class="rm-drawer-body flex-1 overflow-y-auto p-5 space-y-4 text-xs">
                {{-- Contacto Principal de Emergencia --}}
                <div class="p-4 rounded-2xl bg-rose-50/70 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/60 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10.5px] font-black uppercase text-rose-800 dark:text-rose-300 tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-phone-call text-sm"></i>
                            Contacto Principal de Emergencia
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-200/80 text-rose-900 dark:bg-rose-900/60 dark:text-rose-200 uppercase">Prioridad 1</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div>
                            <h4 class="text-sm font-black text-[var(--rm-text-title)]">{{ $adultoMayor->contacto_emergencia_nombre ?: 'Familiar de Referencia' }}</h4>
                            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">{{ $adultoMayor->contacto_emergencia_parentesco ?: 'Apoderado Legal / Familiar directo' }}</p>
                        </div>
                        @if($adultoMayor->contacto_emergencia_celular)
                        <a href="tel:{{ $adultoMayor->contacto_emergencia_celular }}"
                           class="px-3 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition inline-flex items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-phone"></i>
                            <span>{{ $adultoMayor->contacto_emergencia_celular }}</span>
                        </a>
                        @else
                        <span class="text-xs text-[var(--rm-text-muted)] italic">Teléfono no registrado</span>
                        @endif
                    </div>
                </div>

                {{-- Listado de Familiares Registrados --}}
                <div class="space-y-3">
                    <h4 class="font-black text-[var(--rm-text-title)] uppercase text-[11px] tracking-wider text-[var(--rm-text-muted)]">Familiares y Apoderados Registrados</h4>
                    
                    @if($adultoMayor->familiares && $adultoMayor->familiares->count() > 0)
                        @foreach($adultoMayor->familiares as $fam)
                        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#1E3A8A] flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="ph-bold ph-user"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-xs text-[var(--rm-text-title)]">{{ $fam->nombre_completo ?? ($fam->nombres . ' ' . $fam->ap_paterno) }}</h5>
                                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">{{ $fam->pivot->parentesco ?? 'Familiar' }} · {{ $fam->telefono ?: ($fam->celular ?: 'Sin celular') }}</p>
                                </div>
                            </div>
                            @if($fam->telefono || $fam->celular)
                            <a href="tel:{{ $fam->telefono ?: $fam->celular }}"
                               class="h-8 w-8 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-emerald-50 text-emerald-700 flex items-center justify-center transition"
                               title="Llamar">
                                <i class="ph-bold ph-phone text-sm"></i>
                            </a>
                            @endif
                        </div>
                        @endforeach
                    @else
                        {{-- Ejemplo enriquecido si no hay en pivot --}}
                        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-blue-100 dark:bg-blue-950 text-[#1E3A8A] flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="ph-bold ph-user"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-xs text-[var(--rm-text-title)]">{{ $adultoMayor->contacto_emergencia_nombre ?: 'Hijo/a tutor' }}</h5>
                                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Hijo/a · Tutor Legal acreditado</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                Tutor Acreditado
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Frecuencia de Visitas y Acompañamiento --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-2">
                    <span class="text-[10.5px] font-black uppercase text-[var(--rm-text-muted)] tracking-wider block">Régimen de Visitas</span>
                    <p class="text-xs text-[var(--rm-text-body)] leading-relaxed">
                        Visitas autorizadas en horario diurno (14:00 – 18:00). Acompañamiento presencial permitido fines de semana y festivos.
                    </p>
                </div>
            </div>

            {{-- Footer Fijo --}}
            <footer class="p-4 border-t border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-center justify-between gap-3 shrink-0 text-xs">
                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Información social y familiar</span>
                <button type="button"
                        @click="drawerFamilia = false"
                        class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition cursor-pointer">
                    Cerrar Panel
                </button>
            </footer>
        </aside>
    </div>
</div>
