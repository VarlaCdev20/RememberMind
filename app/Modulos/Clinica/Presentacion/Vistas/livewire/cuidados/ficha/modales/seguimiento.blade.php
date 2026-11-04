{{-- Formulario de seguimiento; estructura compartida y estado del componente padre. --}}
@if($modalSeguimiento)
<x-ui.modal-livewire id="ficha-seguimiento" wire:model="modalSeguimiento" closeMethod="cerrarModalSeguimiento" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal" subtitle="Expediente evolutivo asistencial del residente">
    <x-slot name="title">Registrar Evolución Clínica</x-slot>
{{-- Bloque de Trazabilidad Automática --}}
            <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-user-circle text-sm text-[var(--rm-action-primary)]"></i>
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
                    <select wire:model="segEstado" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                        <option value="ESTABLE">Estable</option>
                        <option value="VIGILANCIA">En Vigilancia</option>
                        <option value="DELICADO">Delicado</option>
                        <option value="CRITICO">Crítico</option>
                    </select>
                </div>

                {{-- Alimentación / Ingesta --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Alimentación / Ingesta</label>
                    <select wire:model="segAlimentacion" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                        <option value="COMPLETA">Completa / Buena ingesta</option>
                        <option value="PARCIAL">Parcial / Regular</option>
                        <option value="RECHAZADA">Rechazada</option>
                        <option value="AYUNO">Ayuno indicado</option>
                    </select>
                </div>

                {{-- Movilidad Funcional --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Movilidad Funcional</label>
                    <select wire:model="segMovilidad" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                        <option value="INDEPENDIENTE">Independiente / Autónoma</option>
                        <option value="ASISTIDA">Asistida con apoyo</option>
                        <option value="SILLA_RUEDAS">Silla de ruedas</option>
                        <option value="ENCAMADO">Encamado</option>
                    </select>
                </div>

                {{-- Patrón de Descanso / Sueño --}}
                <div>
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Patrón de Descanso / Sueño</label>
                    <select wire:model="segSueno" class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-2 px-3 text-xs text-[var(--rm-text-title)] font-medium focus:ring-1 focus:ring-[var(--rm-action-primary)]">
                        <option value="NORMAL">Normal / Reparador</option>
                        <option value="INTERRUMPIDO">Interrumpido / Inquieto</option>
                        <option value="INSOMNIO">Insomnio persistente</option>
                        <option value="SOMNOLENCIA">Somnolencia / Sedación</option>
                    </select>
                </div>

                {{-- Opciones de Alerta / Seguimiento --}}
                <div class="col-span-1 sm:col-span-2 p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-2">
                    <label class="flex items-center gap-2 font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] cursor-pointer text-xs">
                        <input type="checkbox" wire:model="segRequiereMedico" class="rounded border-[var(--rm-border)] text-[var(--rm-warning-strong)] focus:ring-[var(--rm-focus-ring-color)]" />
                        <span>Requiere seguimiento o valoración médica complementaria</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] cursor-pointer text-xs">
                        <input type="checkbox" wire:model="segIncidente" class="rounded border-[var(--rm-border)] text-[var(--rm-danger-strong)] focus:ring-[var(--rm-focus-ring-color)]" />
                        <span>Ocurrió un incidente clínico en el turno</span>
                    </label>
                </div>

                {{-- Observación / Nota de Evolución Clínica --}}
                <div class="col-span-1 sm:col-span-2">
                    <label class="font-bold text-[var(--rm-text-title)] block mb-1">Evolución Clínica y Observaciones Asistenciales *</label>
                    <textarea wire:model="segObs" rows="3" placeholder="Detalle de cambios observados, respuesta al tratamiento, estado de ánimo y acciones realizadas..." class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 text-xs text-[var(--rm-text-title)] focus:ring-1 focus:ring-[var(--rm-action-primary)]"></textarea>
                    @error('segObs') <span class="text-[var(--rm-danger-strong)] text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Pie del Modal con Botón Azul Oscuro --}}
    <x-slot name="footer">
<div class="flex items-center justify-between border-t border-[var(--rm-border)] pt-3 text-xs">
                <span class="text-[10.5px] text-[var(--rm-text-muted)] flex items-center gap-1">
                    <i class="ph-bold ph-lock"></i>
                    <span>Registro firmado e inmutable</span>
                </span>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="cerrarModalSeguimiento" class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="guardarSeguimiento" wire:loading.attr="disabled" class="px-4 py-2 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-xs font-bold text-[var(--rm-text-on-primary)] shadow-xs transition inline-flex items-center gap-1.5">
                        <i class="ph-bold ph-check" wire:loading.remove wire:target="guardarSeguimiento"></i>
                        <span wire:loading.remove wire:target="guardarSeguimiento">Registrar evolución</span>
                        <span wire:loading wire:target="guardarSeguimiento">Guardando...</span>
                    </button>
                </div>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
