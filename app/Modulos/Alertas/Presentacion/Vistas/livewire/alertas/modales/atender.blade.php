@if($modalAtender && $alertaActiva)
    <x-ui.modal-livewire id="modalAtenderAlerta" wire:model="modalAtender" maxWidth="md" closeMethod="cerrarModales">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-info)]">
                <i class="ph-bold ph-stethoscope text-lg" aria-hidden="true"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div>
                <span class="block">Atender y registrar evolución</span>
                <span class="mt-0.5 block text-xs font-medium text-[var(--rm-text-secondary)]">La alerta cambiará al estado En atención</span>
            </div>
        </x-slot>

        <div class="space-y-4">
            <x-ui.section-card class="shadow-none" title="Alerta activa" icon="ph-warning-circle">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-[var(--rm-text-primary)]">
                            {{ $alertaActiva->adultoMayor?->ap_paterno }} {{ $alertaActiva->adultoMayor?->ap_materno }} {{ $alertaActiva->adultoMayor?->nombres }}
                        </h4>
                        <p class="mt-0.5 font-mono text-xs text-[var(--rm-text-secondary)]">{{ $alertaActiva->adultoMayor?->ubicacion_texto ?? 'Sin ubicación asignada' }}</p>
                    </div>
                    <x-ui.status-badge :estado="$alertaActiva->prioridad ?? 'MEDIO'" />
                </div>
                <div class="mt-3 border-t border-[var(--rm-border-soft)] pt-3 text-xs">
                    <p class="font-semibold text-[var(--rm-text-body)]"><strong class="text-[var(--rm-text-primary)]">Diagnóstico:</strong> {{ $alertaActiva->tipo }}</p>
                    <p class="mt-1 line-clamp-2 text-[var(--rm-text-secondary)]">{{ $alertaActiva->descripcion }}</p>
                </div>
            </x-ui.section-card>

            <x-ui.form-section title="Intervención asistencial" icon="ph-pencil-simple-line" :columns="1">
                <x-ui.field label="Nota de intervención" for="accionTomadaInput" required error="accionTomada" hint="Mín. 5 caracteres" help="Se registrarán el usuario, la fecha y la hora de esta intervención.">
                    <textarea id="accionTomadaInput" wire:model="accionTomada" rows="4" maxlength="1000" placeholder="Describa la intervención efectuada y la respuesta observada." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('accionTomada')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </div>

        <x-slot name="footer">
            <x-ui.action-button variant="ghost" size="sm" wire:click="cerrarModales">Cancelar</x-ui.action-button>
            <x-ui.action-button variant="primary" size="sm" wire:click="guardarAtencion" loading="guardarAtencion">
                <span wire:loading.remove wire:target="guardarAtencion" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-check" aria-hidden="true"></i>Registrar atención</span>
                <span wire:loading wire:target="guardarAtencion" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-spinner animate-spin" aria-hidden="true"></i>Registrando...</span>
            </x-ui.action-button>
        </x-slot>
    </x-ui.modal-livewire>
@endif
