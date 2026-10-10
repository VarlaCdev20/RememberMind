@if($modalCerrar && $alertaActiva)
    <x-ui.modal-livewire id="modalCerrarAlerta" wire:model="modalCerrar" maxWidth="md" closeMethod="cerrarModales">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-success-soft)] text-[var(--rm-success)]">
                <i class="ph-bold ph-check-circle text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div>
                <span class="block">Cerrar y archivar alerta</span>
                <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">Resolución asistencial con trazabilidad completa</span>
            </div>
        </x-slot>

        <div class="space-y-4">
            <x-ui.section-card class="shadow-none" title="Resumen de la alerta" icon="ph-bell-ringing">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-[var(--rm-text-primary)]">
                            {{ $alertaActiva->adultoMayor?->ap_paterno }} {{ $alertaActiva->adultoMayor?->ap_materno }} {{ $alertaActiva->adultoMayor?->nombres }}
                        </h4>
                        <p class="mt-0.5 font-mono text-xs text-[var(--rm-text-secondary)]">{{ $alertaActiva->adultoMayor?->ubicacion_texto ?? 'Sin ubicación asignada' }}</p>
                    </div>
                    <x-ui.status-badge :estado="$alertaActiva->estado" />
                </div>
                <div class="mt-3 border-t border-[var(--rm-border-soft)] pt-3 text-xs">
                    <p class="font-semibold text-[var(--rm-text-body)]"><strong class="text-[var(--rm-text-primary)]">Alerta:</strong> {{ $alertaActiva->tipo }}</p>
                    <p class="mt-1 line-clamp-2 text-[var(--rm-text-secondary)]">{{ $alertaActiva->descripcion }}</p>
                </div>
            </x-ui.section-card>

            <x-ui.callout variant="success" title="Conservación del historial clínico" icon="ph-shield-check">
                El cierre cambia el estado a Resuelta. La alerta y sus intervenciones permanecen disponibles para auditoría.
            </x-ui.callout>

            <x-ui.form-section title="Justificación del cierre" icon="ph-seal-check" :columns="1">
                <x-ui.field label="Resolución asistencial" for="observacionCierreInput" required error="observacionCierre" hint="Mín. 5 caracteres">
                    <textarea id="observacionCierreInput" wire:model="observacionCierre" rows="4" maxlength="1000" placeholder="Detalle los hallazgos que justifican el cierre de la alerta." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('observacionCierre')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </div>

        <x-slot name="footer">
            <x-ui.action-button variant="ghost" size="sm" wire:click="cerrarModales">Cancelar</x-ui.action-button>
            <x-ui.action-button variant="success" size="sm" wire:click="confirmarCierre" loading="confirmarCierre">
                <span wire:loading.remove wire:target="confirmarCierre" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-archive-box" aria-hidden="true"></i>Confirmar cierre</span>
                <span wire:loading wire:target="confirmarCierre" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i>Cerrando...</span>
            </x-ui.action-button>
        </x-slot>
    </x-ui.modal-livewire>
@endif
