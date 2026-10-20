@if($modalAtender && $alertaActiva)
    <x-ui.modal-livewire id="modalAtenderAlerta" wire:model="modalAtender" maxWidth="md" closeMethod="solicitarCerrarModales" alpineBusy="guardados > 0">
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
                            {{ $alertaActiva->residente?->apellido_paterno }} {{ $alertaActiva->residente?->apellido_materno }} {{ $alertaActiva->residente?->nombres }}
                        </h4>
                        <p class="mt-0.5 font-mono text-xs text-[var(--rm-text-secondary)]">{{ $alertaActiva->residente?->ubicacion_formateada ?? 'Sin ubicación asignada' }}</p>
                    </div>
                    <span class="rm-badge rm-alert-severity {{ in_array($alertaActiva->prioridad, ['CRITICO','CRITICA']) ? 'is-critical' : (in_array($alertaActiva->prioridad, ['ALTO','ALTA']) ? 'is-high' : '') }}">Prioridad {{ $alertaActiva->prioridad }}</span>
                </div>
                <div class="mt-3 border-t border-[var(--rm-border-soft)] pt-3 text-xs">
                    <p class="font-semibold text-[var(--rm-text-body)]"><strong class="text-[var(--rm-text-primary)]">Tipo de alerta:</strong> {{ $alertaActiva->tipo }}</p>
                    <p class="mt-1 line-clamp-2 text-[var(--rm-text-secondary)]">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alertaActiva->descripcion) }}</p>
                </div>
            </x-ui.section-card>

            <x-ui.form-section title="Intervención asistencial" icon="ph-pencil-simple-line" :columns="1">
                <x-ui.field label="Nota de intervención" for="accionTomadaInput" required error="accionTomada" hint="5 a 10.000 caracteres" help="Se registrarán el usuario, la fecha y la hora de esta intervención.">
                    <textarea id="accionTomadaInput" aria-invalid="{{ $errors->has('accionTomada') ? 'true' : 'false' }}" aria-describedby="accionTomadaInput-help {{ $errors->has('accionTomada') ? 'accionTomadaInput-error' : '' }}" wire:model="accionTomada" rows="4" minlength="5" maxlength="10000" placeholder="Describa la intervención efectuada y la respuesta observada." @class(['rm-textarea', 'rm-textarea-error' => $errors->has('accionTomada')])></textarea>
                </x-ui.field>
            </x-ui.form-section>
        </div>

        <x-slot name="footer">
            <div class="w-full space-y-3">
                @include('livewire.alertas.modales.confirmar-descarte')
                <div class="flex flex-wrap justify-end gap-3">
            <x-ui.action-button variant="ghost" size="sm" wire:click="solicitarCerrarModales" x-bind:disabled="guardados > 0">Cancelar</x-ui.action-button>
            <x-ui.action-button variant="primary" size="sm" wire:click="guardarAtencion" loading="guardarAtencion">
                <span wire:loading.remove wire:target="guardarAtencion" class="inline-flex items-center gap-1.5"><i class="ph-bold ph-check" aria-hidden="true"></i>Registrar atención</span>
                <span wire:loading wire:target="guardarAtencion" class="inline-flex items-center gap-1.5">Registrando...</span>
            </x-ui.action-button>
                </div>
            </div>
        </x-slot>
    </x-ui.modal-livewire>
@endif
