<x-ui.drawer-livewire
    wire:model="drawerDosisAbierto"
    title="Administrar medicación"
    subtitle="Detalle de la dosis seleccionada"
    badge="DOSIS Y PRESCRIPCIÓN"
    icon="ph-pill"
    size="md"
    close-method="cerrarDrawerDosis"
>
@if(!empty($dosisDetalle))
    <div class="space-y-4 text-xs">
        {{-- RESIDENTE --}}
        <div class="flex items-center gap-3 pb-3 border-b border-[var(--rm-border-soft)]">
            <div class="w-10 h-10 rounded-full bg-[var(--rm-action-primary)]/10 border border-[var(--rm-action-primary)]/20 flex items-center justify-center font-bold text-sm text-[var(--rm-action-primary)] shrink-0">
                {{ $dosisDetalle['residente']['iniciales'] ?? 'SR' }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="font-bold text-sm text-[var(--rm-text-primary)] truncate">
                    {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Sin residente' }}
                </div>
                <div class="text-[11.5px] text-[var(--rm-text-secondary)] flex items-center gap-2 mt-0.5 flex-wrap">
                    <span>{{ $dosisDetalle['residente']['edad'] ?? 'Edad no registrada' }}</span>
                    <span>·</span>
                    <span>{{ $dosisDetalle['residente']['habitacion'] ?? 'Sin habitación' }}</span>
                    <span>·</span>
                    <span class="font-mono text-[11px]">NHC: {{ $dosisDetalle['residente']['nhc'] ?? 'No registrado' }}</span>
                </div>
            </div>
        </div>

        {{-- MEDICAMENTO --}}
        <div class="pt-1">
            <div class="text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)] mb-1.5">
                Medicamento
            </div>
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-[var(--rm-action-primary)] text-white flex items-center justify-center shrink-0 shadow-2xs">
                        <i class="ph-bold ph-pill text-base"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-xs sm:text-[13px] text-[var(--rm-text-primary)] truncate">
                            {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento no identificado' }}
                        </div>
                        <div class="text-[11px] text-[var(--rm-text-secondary)] truncate">
                            {{ $dosisDetalle['medicamento']['concentracion'] ?? 'Concentración no registrada' }} · {{ $dosisDetalle['medicamento']['forma'] ?? 'Forma no registrada' }}
                        </div>
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="abrirModalMedicamento('{{ $dosisDetalle['cod_medicamento'] ?? '' }}')"
                    class="shrink-0 px-2.5 py-1 text-xs font-semibold rounded-lg bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition shadow-2xs">
                    Ver ficha
                </button>
            </div>
        </div>

        {{-- PRESCRIPCIÓN MÉDICA --}}
        <div class="pt-2 space-y-2">
            <div class="text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)]">
                Prescripción Médica
            </div>

            <div class="grid grid-cols-2 gap-y-1.5 text-xs py-1">
                <span class="text-[var(--rm-text-secondary)]">Dosis:</span>
                <span class="font-bold text-[var(--rm-text-primary)] text-right font-mono">{{ $dosisDetalle['prescripcion']['dosis'] ?? '20 mg' }}</span>

                <span class="text-[var(--rm-text-secondary)]">Vía:</span>
                <span class="font-semibold text-[var(--rm-text-primary)] text-right">{{ $dosisDetalle['prescripcion']['via'] ?? 'Oral' }}</span>

                <span class="text-[var(--rm-text-secondary)]">Frecuencia:</span>
                <span class="font-semibold text-[var(--rm-text-primary)] text-right">{{ $dosisDetalle['prescripcion']['frecuencia'] ?? 'Cada 24 horas' }}</span>
            </div>

            {{-- Indicación médica --}}
            <div class="pt-1 text-xs">
                <span class="text-[var(--rm-text-secondary)] block text-[11px] font-medium">Indicación:</span>
                <p class="text-[var(--rm-text-primary)] mt-0.5 leading-relaxed bg-[var(--rm-surface-soft)]/60 p-2.5 rounded-xl border border-[var(--rm-border-soft)]">
                    {{ $dosisDetalle['prescripcion']['indicacion'] ?? 'Tomar 1 cápsula en ayunas 30 minutos antes del desayuno.' }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-y-1.5 text-xs pt-1">
                <span class="text-[var(--rm-text-secondary)]">Prescrito por:</span>
                <span class="font-semibold text-[var(--rm-text-primary)] text-right truncate">{{ $dosisDetalle['prescripcion']['prescriptor'] ?? 'Prescriptor no registrado' }}</span>

                <span class="text-[var(--rm-text-secondary)]">Programación del Horario:</span>
                <span class="font-mono font-bold text-[var(--rm-text-primary)] text-right">{{ $dosisDetalle['programacion']['hora_programada'] ?? $dosisDetalle['hora'] ?? '08:00' }}</span>
            </div>
        </div>

        {{-- SEGURIDAD Y ALERGIAS --}}
        <div class="pt-2 space-y-2">
            <div class="text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)]">
                Seguridad y 5 Correctos
            </div>

            <div class="grid grid-cols-2 gap-1.5 text-[11.5px] py-1 font-semibold text-[var(--rm-action-primary)]">
                <div class="flex items-center gap-1.5">
                    <i class="ph-bold ph-check-circle text-[var(--rm-action-primary)]"></i>
                    <span>Paciente correcto</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="ph-bold ph-check-circle text-[var(--rm-action-primary)]"></i>
                    <span>Medicamento correcto</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="ph-bold ph-check-circle text-[var(--rm-action-primary)]"></i>
                    <span>Dosis correcta</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="ph-bold ph-check-circle text-[var(--rm-action-primary)]"></i>
                    <span>Vía correcta</span>
                </div>
                <div class="flex items-center gap-1.5 col-span-2">
                    <i class="ph-bold ph-check-circle text-[var(--rm-action-primary)]"></i>
                    <span>Hora correcta</span>
                </div>
            </div>

            {{-- Alergias --}}
            <div>
                @if(empty($dosisDetalle['seguridad']['alergias']) || str_contains($dosisDetalle['seguridad']['alergias'], 'Sin alergias'))
                    <div class="p-2 rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30 text-[11px] font-semibold flex items-center gap-1.5">
                        <i class="ph-bold ph-shield-check text-base"></i>
                        <span>Sin alergias medicamentosas registradas</span>
                    </div>
                @else
                    <div class="p-2 rounded-xl bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/30 text-[11px] font-semibold flex items-center gap-1.5">
                        <i class="ph-bold ph-warning-octagon text-base"></i>
                        <span>{{ $dosisDetalle['seguridad']['alergias'] }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- SEGUIMIENTO CLÍNICO --}}
        <div class="pt-2 text-xs">
            <div class="flex items-center justify-between text-[11.5px] text-[var(--rm-text-secondary)]">
                <span>Último seguimiento:</span>
                <span class="font-medium text-[var(--rm-text-primary)]">
                    {{ $dosisDetalle['seguimiento']['ultima_admin'] ?? 'Sin administraciones previas' }}
                </span>
            </div>
        </div>

        {{-- OBSERVACIÓN DE ENFERMERÍA --}}
        @if(empty($dosisDetalle['ya_registrada']))
            <div class="pt-2">
                <label for="obsEnfDrawer" class="block text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-action-primary)] mb-1.5">
                    Observación de Enfermería
                </label>
                <div class="relative">
                    <textarea
                        id="obsEnfDrawer"
                        wire:model="observacionEnfermeria"
                        maxlength="200"
                        rows="3"
                        placeholder="Añadir observación de administración (opcional)..."
                        class="rm-input w-full h-[76px] text-xs p-2.5 resize-none"></textarea>
                    <span class="absolute bottom-2 right-2.5 text-[10px] text-[var(--rm-text-secondary)] font-mono pointer-events-none">
                        {{ strlen($observacionEnfermeria) }}/200
                    </span>
                </div>
            </div>
        @endif
    </div>
@else
    <div class="py-14 text-center text-[var(--rm-text-secondary)]">
        <i class="ph-bold ph-spinner animate-spin text-2xl mb-2 text-[var(--rm-action-primary)] block"></i>
        Cargando detalle de la dosis...
    </div>
@endif

    <x-slot:footer>
        @if(!empty($dosisDetalle) && empty($dosisDetalle['ya_registrada']))
            <div class="grid grid-cols-2 gap-2.5">
                <button
                    type="button"
                    wire:click="abrirModalOmision"
                    class="rm-btn rm-btn-danger rm-btn-sm h-10 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-warning-circle text-base"></i>
                    <span>Registrar omisión</span>
                </button>

                <button
                    type="button"
                    wire:click="abrirModalAdministrar"
                    wire:loading.attr="disabled"
                    class="rm-btn rm-btn-primary rm-btn-sm h-10 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-check-circle text-base" wire:loading.remove wire:target="administrarDosisConfirmada"></i>
                    <span wire:loading.remove wire:target="administrarDosisConfirmada">Administrar</span>
                    <span wire:loading wire:target="administrarDosisConfirmada">Guardando...</span>
                </button>
            </div>
        @elseif(!empty($dosisDetalle) && !empty($dosisDetalle['ya_registrada']))
            <div class="p-2.5 rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] text-center text-xs font-bold border border-[var(--rm-action-primary)]/30">
                Esta dosis ya ha sido registrada en el sistema.
            </div>
        @endif
    </x-slot:footer>
</x-ui.drawer-livewire>
