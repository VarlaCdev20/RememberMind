@if($modalCerrar && $alertaActiva)
    <x-ui.modal-livewire id="modalCerrarAlerta" wire:model="modalCerrar" maxWidth="md" closeMethod="solicitarCerrarModales" alpineBusy="guardados > 0">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-success-soft)] text-[var(--rm-success)]">
                <i class="ph-bold ph-check-circle text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div>
                <span class="block">{{ $puedeIntervenir ? 'Cerrar y archivar alerta' : 'Cerrar coordinación de la alerta' }}</span>
                <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">Resultado y justificación con trazabilidad completa</span>
            </div>
        </x-slot>

        <div class="space-y-4">
            <x-ui.section-card class="shadow-none" title="Resumen de la alerta" icon="ph-bell-ringing">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-[var(--rm-text-primary)]">
                            {{ $alertaActiva->residente?->apellido_paterno }} {{ $alertaActiva->residente?->apellido_materno }} {{ $alertaActiva->residente?->nombres }}
                        </h4>
                        <p class="mt-0.5 font-mono text-xs text-[var(--rm-text-secondary)]">{{ $alertaActiva->residente?->ubicacion_formateada ?? 'Sin ubicación asignada' }}</p>
                    </div>
                    <x-ui.status-badge :estado="$alertaActiva->estado" />
                </div>
                <div class="mt-3 border-t border-[var(--rm-border-soft)] pt-3 text-xs">
                    <p class="font-semibold text-[var(--rm-text-body)]"><strong class="text-[var(--rm-text-primary)]">Alerta:</strong> {{ $alertaActiva->tipo }}</p>
                    <p class="mt-1 line-clamp-2 text-[var(--rm-text-secondary)]">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alertaActiva->descripcion) }}</p>
                </div>
            </x-ui.section-card>

            <x-ui.callout variant="success" title="Conservación del historial de la alerta" icon="ph-shield-check">
                El cierre cambia el estado a CERRADA. La alerta y sus eventos permanecen disponibles. Un cierre de coordinación no acredita resolución clínica.
            </x-ui.callout>

            <x-ui.form-section title="Justificación del cierre" icon="ph-seal-check" :columns="1">
                <x-ui.field label="Resultado y justificación" for="observacionCierreInput" required error="observacionCierre" hint="5 a 10.000 caracteres">
                    <textarea id="observacionCierreInput" aria-invalid="{{ $errors->has('observacionCierre') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('observacionCierre') ? 'observacionCierreInput-error' : '' }}" wire:model="observacionCierre" rows="4" minlength="5" maxlength="10000" placeholder="Describe el resultado y las razones del cierre según tu responsabilidad." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('observacionCierre')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </div>

        <x-slot name="footer">
            <div class="w-full space-y-3">
                @include('livewire.alertas.modales.confirmar-descarte')
                <div class="flex flex-wrap justify-end gap-3">
            <x-ui.action-button variant="ghost" size="sm" wire:click="solicitarCerrarModales" x-bind:disabled="guardados > 0">Cancelar</x-ui.action-button>
            <x-ui.action-button variant="success" size="sm" wire:click="confirmarCierre" loading="confirmarCierre">
                <span wire:loading.remove wire:target="confirmarCierre" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-archive-box" aria-hidden="true"></i>Confirmar cierre</span>
                <span wire:loading wire:target="confirmarCierre" class="inline-flex items-center gap-1.5">Cerrando...</span>
            </x-ui.action-button>
                </div>
            </div>
        </x-slot>
    </x-ui.modal-livewire>
@endif
