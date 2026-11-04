{{-- Formulario de incidente; estructura compartida y estado del componente padre. --}}
@if($modalIncidente)
<x-ui.modal-livewire id="ficha-incidente" wire:model="modalIncidente" closeMethod="cerrarModalIncidente" maxWidth="md" :showValidation="false" class="rm-clinical-form-modal" subtitle="Generación de alerta clínica inmediata para el equipo.">
    <x-slot name="title"><i class="ph-bold ph-warning-octagon text-lg"></i>
                <span>Reportar Incidente Asistencial</span></x-slot>
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
                    @error('incidenteMotivo') <span class="text-[var(--rm-danger-strong)] text-[10px] font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
    <x-slot name="footer">
<div class="flex justify-end gap-2">
                <button type="button" wire:click="cerrarModalIncidente" class="rounded-xl bg-[var(--rm-surface-soft)] px-4 py-2 text-xs font-bold text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface-soft)] transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardarIncidente" wire:loading.attr="disabled" class="rounded-xl bg-[var(--rm-danger-action)] px-4 py-2 text-xs font-bold text-[var(--rm-text-inverse)] hover:bg-[var(--rm-danger-action)] transition">
                    <span wire:loading.remove wire:target="guardarIncidente">Emitir reporte de alerta</span>
                    <span wire:loading wire:target="guardarIncidente">Emitiendo...</span>
                </button>
            </div>
    </x-slot>
</x-ui.modal-livewire>
@endif
