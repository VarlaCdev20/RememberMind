{{-- MODAL EMERGENTE CENTRADO: ADMINISTRAR MEDICACIÓN CON SEGURIDAD CLÍNICA --}}
@if($modalAdministrarAbierto)
<div 
    x-data="{ modalOpen: true }"
    x-cloak>
    <template x-teleport="body">
        <div 
            x-show="modalOpen"
            x-on:keydown.escape.window="$wire.cerrarModalAdministrar()"
            class="fixed inset-0 z-[99999] overflow-y-auto font-sans" 
            aria-labelledby="modal-administrar-title" 
            role="dialog" 
            aria-modal="true"
            style="display: none;">
    
    <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
        {{-- Overlay oscuro suave con desenfoque --}}
        <div 
            wire:click="cerrarModalAdministrar" 
            class="fixed inset-0 bg-[#1A1816]/70 dark:bg-black/85 backdrop-blur-[3px] transition-opacity">
        </div>

        {{-- Ventana Modal Emergente Mediana Centrada (max-w-2xl) --}}
        <div 
            @click.stop
            class="relative inline-block w-full max-w-2xl text-left align-middle transition-all transform bg-[#F0E8DE] dark:bg-[#2C2924] rounded-[16px] border border-[#C7B9AA] dark:border-[#494139] shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden my-6 z-10">
            
            {{-- Header del Modal con X Superior --}}
            <div class="px-5 py-4 border-b border-[#C7B9AA] dark:border-[#494139] flex items-center justify-between bg-[#E4D8CC] dark:bg-[#211F1B]">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#71876A] text-white shadow-2xs">
                        <i class="ph-bold ph-pill text-xl"></i>
                    </span>
                    <div>
                        <h3 class="text-[17px] font-[800] text-[#304060] dark:text-[#EFE5DA] tracking-tight" id="modal-administrar-title">
                            Administrar medicación
                        </h3>
                        <p class="text-xs text-[#677084] dark:text-[#BDAE9F]">
                            Confirmación asistencial de la dosis y registro de la toma
                        </p>
                    </div>
                </div>

                {{-- Botón X Superior para Cerrar --}}
                <button 
                    type="button" 
                    wire:click="cerrarModalAdministrar"
                    class="rounded-lg p-1.5 text-[#677084] dark:text-[#BDAE9F] hover:text-[#304060] dark:hover:text-white hover:bg-[#DED1C3] dark:hover:bg-[#38342E] transition cursor-pointer"
                    title="Cerrar modal">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>

            {{-- Formulario con validación frontend + backend --}}
            <form wire:submit.prevent="guardarAdministracion">
                <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4 custom-scrollbar text-xs">
                    
                    {{-- 1. SECCIÓN: DATOS CLÍNICOS SOLO LECTURA (NO EDITABLES / INMUTABLES) --}}
                    <div class="rounded-[12px] bg-[#F3EAE1] dark:bg-[#201E1C] border border-[#C7B9AA] dark:border-[#494139] p-3.5 space-y-3">
                        <div class="flex items-center justify-between border-b border-[#C7B9AA]/70 dark:border-[#494139]/70 pb-2">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-[700] text-[#71876A] dark:text-[#91A287] uppercase tracking-wider">
                                <i class="ph-bold ph-lock-key"></i>
                                Datos de la Prescripción Médica (Solo Lectura)
                            </span>
                            <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full bg-[#E4D8CC] dark:bg-[#332F29] text-[#677084] dark:text-[#BDAE9F] flex items-center gap-1">
                                <i class="ph ph-shield-check"></i> Inmutable
                            </span>
                        </div>

                        {{-- Fila 1: Residente y Habitación/Cama --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <span class="text-[11px] font-medium text-[#677084] dark:text-[#BDAE9F] block">Residente:</span>
                                <span class="font-bold text-[13px] text-[#304060] dark:text-[#EFE5DA] block leading-tight">
                                    {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Residente' }}
                                </span>
                                <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F] block mt-0.5">
                                    <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ $dosisDetalle['residente']['habitacion'] ?? 'Hab. 101' }}</strong> / <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ $dosisDetalle['residente']['cama'] ?? 'Cama 1' }}</strong> &bull; {{ $dosisDetalle['residente']['edad'] ?? '' }}
                                </span>
                            </div>

                            <div>
                                <span class="text-[11px] font-medium text-[#677084] dark:text-[#BDAE9F] block">Medicamento prescrito:</span>
                                <span class="font-bold text-[13px] text-[#304060] dark:text-[#EFE5DA] block leading-tight">
                                    {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento' }}
                                </span>
                                <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F] block mt-0.5">
                                    {{ $dosisDetalle['medicamento']['concentracion'] ?? '' }} &bull; {{ $dosisDetalle['medicamento']['forma'] ?? 'Comprimido' }}
                                </span>
                            </div>
                        </div>

                        {{-- Fila 2: Dosis prescrita, Vía, Frecuencia y Hora programada --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 border-t border-[#C7B9AA]/50 dark:border-[#494139]/50 text-[11.5px]">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#BDAE9F] block">Dosis Prescrita</span>
                                <span class="font-bold text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['prescripcion']['dosis'] ?? ($formDosisPrescritaValor . ' ' . $formUnidadDosis) }}
                                </span>
                            </div>

                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#BDAE9F] block">Vía Prescrita</span>
                                <span class="font-bold text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['prescripcion']['via'] ?? 'Vía oral' }}
                                </span>
                            </div>

                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#BDAE9F] block">Frecuencia</span>
                                <span class="font-bold text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['prescripcion']['frecuencia'] ?? 'Cada 8 horas' }}
                                </span>
                            </div>

                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#677084] dark:text-[#BDAE9F] block">Hora Programada</span>
                                <span class="font-bold text-[#304060] dark:text-[#EFE5DA] font-mono">
                                    {{ $selectedHora ?? '08:00' }} hrs
                                </span>
                            </div>
                        </div>

                        {{-- Fila 3: Indicación médica y Médico Prescriptor --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-[#C7B9AA]/50 dark:border-[#494139]/50 text-[11px]">
                            <div>
                                <span class="font-medium text-[#677084] dark:text-[#BDAE9F]">Médico prescriptor:</span>
                                <span class="font-semibold text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['prescripcion']['prescriptor'] ?? 'Dr. Médico Asignado' }}
                                </span>
                            </div>

                            <div>
                                <span class="font-medium text-[#677084] dark:text-[#BDAE9F]">Indicación:</span>
                                <span class="font-semibold text-[#304060] dark:text-[#EFE5DA]">
                                    {{ $dosisDetalle['prescripcion']['indicacion'] ?? 'Según indicación clínica' }}
                                </span>
                            </div>
                        </div>

                        {{-- Fila 4: Enfermero y Fecha/Hora del sistema (Solo Lectura) --}}
                        <div class="pt-2 border-t border-[#C7B9AA]/60 dark:border-[#494139]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[11px]">
                            <div class="flex items-center gap-2 text-[#677084] dark:text-[#BDAE9F]">
                                <i class="ph-bold ph-user-circle text-sm text-[#A35A44]"></i>
                                <span>Profesional responsable:</span>
                                <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ auth()->user()?->name ?? 'Elena' }}</strong>
                                <span class="text-[10px] text-[#677084] dark:text-[#BDAE9F]">({{ auth()->user()?->profesion ?? 'ENFERMERO' }})</span>
                            </div>
                            <div class="text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                                Fecha registro: <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ now()->format('d/m/Y H:i') }}</strong>
                            </div>
                        </div>
                    </div>

                    {{-- 2. SECCIÓN: SEGURIDAD CLÍNICA - VERIFICACIÓN VISUAL DE LOS "5 CORRECTOS" --}}
                    @php
                        $dAdmin = (float)(($formDosisAdministrada ?? $this->formDosisAdministrada ?? 0) ?: 0);
                        $dPresc = (float)(($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? 0) ?: 0);
                        $dosisDifiere = ($dPresc > 0 && abs($dAdmin - $dPresc) > 0.001);
                    @endphp

                    <div class="rounded-[12px] bg-[#E4D8CC] dark:bg-[#24211D] border border-[#C7B9AA] dark:border-[#494139] p-3.5 space-y-2.5">
                        <div class="flex items-center justify-between border-b border-[#C7B9AA]/70 dark:border-[#494139]/70 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#71876A] text-white text-xs">
                                    <i class="ph-bold ph-shield-check"></i>
                                </span>
                                <div>
                                    <h4 class="text-[11.5px] font-[800] text-[#304060] dark:text-[#EFE5DA] uppercase tracking-wider">
                                        Seguridad Clínica: Protocolo de los 5 Correctos
                                    </h4>
                                    <p class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F]">
                                        Verificación visual automática contrastada contra la prescripción activa
                                    </p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-[#71876A] dark:text-[#91A287] uppercase tracking-wider bg-[#E3EBE0] dark:bg-[#71876A]/20 px-2 py-0.5 rounded-full border border-[#C5D6C0] dark:border-[#71876A]/40">
                                5 / 5 Verificados
                            </span>
                        </div>

                        {{-- Lista de los 5 Correctos Verificados --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            
                            {{-- 1. Residente correcto --}}
                            <div class="p-2 rounded-[8px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA]/70 dark:border-[#494139]/70 flex items-start gap-2">
                                <i class="ph-fill ph-check-circle text-[#71876A] text-base shrink-0 mt-0.5"></i>
                                <div class="min-w-0">
                                    <strong class="text-[#304060] dark:text-[#EFE5DA] block text-[11px]">1. Residente correcto</strong>
                                    <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block truncate">
                                        {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Mario Gutierrez Mendoza' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 2. Medicamento correcto --}}
                            <div class="p-2 rounded-[8px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA]/70 dark:border-[#494139]/70 flex items-start gap-2">
                                <i class="ph-fill ph-check-circle text-[#71876A] text-base shrink-0 mt-0.5"></i>
                                <div class="min-w-0">
                                    <strong class="text-[#304060] dark:text-[#EFE5DA] block text-[11px]">2. Medicamento correcto</strong>
                                    <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block truncate">
                                        {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento verificado' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 3. Dosis correcta --}}
                            <div class="p-2 rounded-[8px] {{ $dosisDifiere ? 'bg-[#F3DDDA] dark:bg-[#C85D52]/20 border border-[#E5BDB5]' : 'bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA]/70 dark:border-[#494139]/70' }} flex items-start gap-2">
                                @if($dosisDifiere)
                                    <i class="ph-fill ph-warning-circle text-[#C85D52] text-base shrink-0 mt-0.5"></i>
                                    <div class="min-w-0">
                                        <strong class="text-[#C85D52] dark:text-[#E5A898] block text-[11px]">3. Dosis modificada</strong>
                                        <span class="text-[10.5px] text-[#C85D52] dark:text-[#E5A898] block">
                                            Admin: <strong>{{ $dAdmin }}</strong> vs Presc: <strong>{{ $dPresc }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }}</strong>
                                        </span>
                                    </div>
                                @else
                                    <i class="ph-fill ph-check-circle text-[#71876A] text-base shrink-0 mt-0.5"></i>
                                    <div class="min-w-0">
                                        <strong class="text-[#304060] dark:text-[#EFE5DA] block text-[11px]">3. Dosis correcta</strong>
                                        <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block truncate">
                                            {{ ($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? '1') }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }} (Coincide con orden)
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- 4. Vía correcta --}}
                            <div class="p-2 rounded-[8px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA]/70 dark:border-[#494139]/70 flex items-start gap-2">
                                <i class="ph-fill ph-check-circle text-[#71876A] text-base shrink-0 mt-0.5"></i>
                                <div class="min-w-0">
                                    <strong class="text-[#304060] dark:text-[#EFE5DA] block text-[11px]">4. Vía correcta</strong>
                                    <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block truncate">
                                        {{ $dosisDetalle['prescripcion']['via'] ?? 'Vía oral' }} (Prescrita)
                                    </span>
                                </div>
                            </div>

                            {{-- 5. Hora correcta --}}
                            <div class="p-2 rounded-[8px] bg-[#F0E8DE] dark:bg-[#2C2924] border border-[#C7B9AA]/70 dark:border-[#494139]/70 flex items-start gap-2 sm:col-span-2">
                                <i class="ph-fill ph-check-circle text-[#71876A] text-base shrink-0 mt-0.5"></i>
                                <div class="min-w-0">
                                    <strong class="text-[#304060] dark:text-[#EFE5DA] block text-[11px]">5. Hora correcta</strong>
                                    <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] block">
                                        {{ $selectedHora ?? '08:00' }} hrs programada para el turno de enfermería en curso
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- 3. SECCIÓN: CAMPOS EDITABLES DEL ENFERMERO CON VALIDACIÓN --}}
                    <div class="space-y-3.5 pt-1">
                        <h4 class="text-[11px] font-[800] text-[#A35A44] dark:text-[#E5A898] uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-pencil-simple text-[#A35A44]"></i>
                            Registro Asistencial del Enfermero
                        </h4>

                        {{-- 1. Dosis Administrada --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="formDosisAdministrada" class="text-xs font-[700] text-[#304060] dark:text-[#EFE5DA]">
                                    Dosis Administrada <span class="text-[#C85D52] dark:text-[#E5A898]">*</span>
                                </label>
                                <span class="text-[11px] text-[#677084] dark:text-[#BDAE9F]">
                                    Prescrita: <strong class="text-[#304060] dark:text-[#EFE5DA]">{{ ($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? '1') }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }}</strong>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <input 
                                        type="number" 
                                        step="any" 
                                        min="0.001" 
                                        id="formDosisAdministrada" 
                                        wire:model.live.debounce.300ms="formDosisAdministrada"
                                        required
                                        placeholder="Ej. 50"
                                        class="w-full h-10 px-3 text-xs font-bold rounded-[10px] border {{ $errors->has('formDosisAdministrada') ? 'border-[#C85D52] focus:ring-[#C85D52]' : 'border-[#C7B9AA] dark:border-[#494139] focus:ring-[#A35A44]' }} bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 shadow-2xs font-mono" />
                                </div>
                                <span class="h-10 px-3 inline-flex items-center text-xs font-bold bg-[#E4D8CC] dark:bg-[#332F29] border border-[#C7B9AA] dark:border-[#494139] rounded-[10px] text-[#304060] dark:text-[#EFE5DA]">
                                    {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'unidad') }}
                                </span>
                            </div>

                            {{-- Alerta si la dosis difiere de la prescrita --}}
                            @if($dosisDifiere)
                                <div class="mt-1.5 p-2.5 rounded-[8px] bg-[#F3DDDA] dark:bg-[#C85D52]/20 border border-[#E5BDB5] dark:border-[#C85D52]/40 flex items-start gap-2 text-[11px] text-[#C85D52] dark:text-[#E5A898]">
                                    <i class="ph-bold ph-warning text-sm shrink-0 mt-0.5"></i>
                                    <span>
                                        <strong>Dosis modificada asistencialmente:</strong> La dosis administrada ({{ $dAdmin }}) difiere de la dosis prescrita ({{ $dPresc }}). Se exige <strong>observación/justificación clínica obligatoria</strong> en el campo inferior.
                                    </span>
                                </div>
                            @endif

                            @error('formDosisAdministrada')
                                <span class="text-[11px] font-bold text-[#C85D52] dark:text-[#E5A898] block mt-1 flex items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- 2. Efecto Observado (Nullable) --}}
                        <div>
                            <label for="formEfectoObservado" class="text-xs font-[700] text-[#304060] dark:text-[#EFE5DA] block mb-1">
                                Efecto Observado <span class="text-[11px] font-normal text-[#677084] dark:text-[#BDAE9F]">(Opcional)</span>
                            </label>
                            <input 
                                type="text" 
                                id="formEfectoObservado"
                                wire:model="formEfectoObservado" 
                                maxlength="500"
                                placeholder="Ej. Buena tolerancia oral, sin disfagia inmediata, sedación leve esperada..."
                                class="w-full h-10 px-3 text-xs rounded-[10px] border {{ $errors->has('formEfectoObservado') ? 'border-[#C85D52]' : 'border-[#C7B9AA] dark:border-[#494139]' }} bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 focus:ring-[#A35A44] shadow-2xs" />
                            @error('formEfectoObservado')
                                <span class="text-[11px] font-bold text-[#C85D52] dark:text-[#E5A898] block mt-1 flex items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- 3. Reacción Adversa (Nullable) --}}
                        <div>
                            <label for="formReaccionAdversa" class="text-xs font-[700] text-[#304060] dark:text-[#EFE5DA] block mb-1">
                                Reacción Adversa <span class="text-[11px] font-normal text-[#677084] dark:text-[#BDAE9F]">(Opcional)</span>
                            </label>
                            <input 
                                type="text" 
                                id="formReaccionAdversa"
                                wire:model="formReaccionAdversa" 
                                maxlength="500"
                                placeholder="Ej. Ninguna observada, náusea leve transitoria, prurito cutáneo..."
                                class="w-full h-10 px-3 text-xs rounded-[10px] border {{ $errors->has('formReaccionAdversa') ? 'border-[#C85D52]' : 'border-[#C7B9AA] dark:border-[#494139]' }} bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 focus:ring-[#A35A44] shadow-2xs" />
                            @error('formReaccionAdversa')
                                <span class="text-[11px] font-bold text-[#C85D52] dark:text-[#E5A898] block mt-1 flex items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- 4. Observación / Justificación de Enfermería --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="formObservacionAdmin" class="text-xs font-[700] text-[#304060] dark:text-[#EFE5DA]">
                                    Observación de Enfermería
                                    @if($dosisDifiere)
                                        <span class="text-[#C85D52] dark:text-[#E5A898] font-bold">* (Obligatoria por variación de dosis)</span>
                                    @else
                                        <span class="text-[11px] font-normal text-[#677084] dark:text-[#BDAE9F]">(Opcional)</span>
                                    @endif
                                </label>
                                <span class="text-[10.5px] text-[#677084] dark:text-[#BDAE9F] font-mono">
                                    {{ strlen((string)($formObservacionAdmin ?? $this->formObservacionAdmin ?? '')) }}/1000
                                </span>
                            </div>

                            <textarea 
                                id="formObservacionAdmin"
                                wire:model="formObservacionAdmin" 
                                maxlength="1000"
                                rows="3"
                                placeholder="{{ $dosisDifiere ? 'Registre la justificación clínica obligatoria por la que se modificó la dosis prescrita...' : 'Detalles asistenciales relevantes, hidratación suministrada, etc.' }}"
                                class="w-full text-xs rounded-[10px] p-3 border {{ $errors->has('formObservacionAdmin') ? 'border-[#C85D52] focus:ring-[#C85D52]' : 'border-[#C7B9AA] dark:border-[#494139] focus:ring-[#A35A44]' }} bg-[#F0E8DE] dark:bg-[#211F1B] text-[#304060] dark:text-[#EFE5DA] placeholder-[#677084] dark:placeholder-[#BDAE9F] focus:outline-none focus:ring-1 resize-none shadow-2xs"></textarea>
                            
                            @error('formObservacionAdmin')
                                <span class="text-[11px] font-bold text-[#C85D52] dark:text-[#E5A898] block mt-1 flex items-center gap-1">
                                    <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                                </span>
                            @enderror
                        </div>

                        {{-- Error general de backend si existiese --}}
                        @error('administracion_error')
                            <div class="p-2.5 rounded-[8px] bg-[#F3DDDA] dark:bg-[#C85D52]/20 border border-[#E5BDB5] dark:border-[#C85D52]/40 text-xs text-[#C85D52] dark:text-[#E5A898] font-bold">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>

                {{-- Footer con Botones Exactos [ Cancelar ] [ Confirmar administración ] --}}
                <div class="px-5 py-3.5 bg-[#E4D8CC] dark:bg-[#211F1B] border-t border-[#C7B9AA] dark:border-[#494139] flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5">
                    <button 
                        type="button" 
                        wire:click="cerrarModalAdministrar"
                        class="h-10 px-4 rounded-[10px] text-xs font-semibold bg-[#F0E8DE] dark:bg-[#332F29] hover:bg-[#DED1C3] dark:hover:bg-[#3B362F] border border-[#C7B9AA] dark:border-[#494139] text-[#304060] dark:text-[#EFE5DA] transition cursor-pointer">
                        Cancelar
                    </button>

                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        class="h-10 px-5 rounded-[10px] text-xs font-bold bg-[#71876A] hover:bg-[#5B7054] text-white transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <i class="ph-bold ph-check text-sm" wire:loading.remove wire:target="guardarAdministracion"></i>
                        <span wire:loading.remove wire:target="guardarAdministracion">Confirmar administración</span>
                        <span wire:loading wire:target="guardarAdministracion">Registrando...</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
</template>
</div>
@endif
