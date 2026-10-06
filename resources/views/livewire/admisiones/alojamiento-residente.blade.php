<div>
    <x-ui.modal-livewire id="alojamiento-residente" wire:model="modalAbierto" title="Alojamiento del residente"
        subtitle="Cambio de cama dentro de una admisión formal vigente" max-width="lg" close-method="cerrarModal">
        <x-slot:icon><i class="ph-bold ph-bed" aria-hidden="true"></i></x-slot:icon>
        @if($residente)
            <div class="space-y-4">
                <div class="rm-card p-4">
                    <p class="font-semibold text-[var(--rm-text-primary)]">{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</p>
                    <p class="mt-1 text-sm text-[var(--rm-text-secondary)]">
                        @if($residente->ocupacionActiva)
                            Ubicación actual: habitación {{ $residente->ocupacionActiva->cama->habitacion->codigo }} · cama {{ $residente->ocupacionActiva->cama->codigo }}
                            @if($residente->ocupacionActiva->cama->habitacion->piso) · piso {{ $residente->ocupacionActiva->cama->habitacion->piso }} @endif
                        @else
                            Sin ocupación activa. Se conservará la admisión y el historial de alojamiento.
                        @endif
                    </p>
                </div>
                @if($impedimento)
                    <p class="rm-alert rm-alert--warning" role="alert">{{ $impedimento }}</p>
                @elseif($camas->isEmpty())
                    <p class="rm-alert rm-alert--info" role="status">No hay camas disponibles en habitaciones habilitadas. La ubicación actual se conserva.</p>
                @else
                    <div>
                        <label for="alojamiento-cama" class="rm-label">Cama de destino <span aria-hidden="true">*</span></label>
                        <x-ui.selector id="alojamiento-cama" label="Cama de destino" wire:model.live="codCama" required>
                            <option value="">Seleccione una cama disponible</option>
                            @foreach($camas as $cama)
                                <option value="{{ $cama->cod_cama }}">{{ $cama->habitacion->piso ? 'Piso '.$cama->habitacion->piso.' · ' : '' }}Habitación {{ $cama->habitacion->codigo }} · cama {{ $cama->codigo }}</option>
                            @endforeach
                        </x-ui.selector>
                        @error('codCama')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                        @error('cod_cama')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="alojamiento-fecha" class="rm-label">Fecha del cambio <span aria-hidden="true">*</span></label>
                            <x-ui.calendario id="alojamiento-fecha" label="Fecha del cambio" wire:model.live="fecha" />
                            @error('fecha')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="alojamiento-hora" class="rm-label">Hora del cambio <span aria-hidden="true">*</span></label>
                            <input id="alojamiento-hora" type="time" step="1" wire:model="hora" class="rm-input" required>
                            @error('hora')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @error('fecha_hora')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                    <div>
                        <label for="alojamiento-motivo" class="rm-label">Motivo u observaciones del cambio</label>
                        <textarea id="alojamiento-motivo" wire:model="motivo" class="rm-input" rows="3" maxlength="1000"></textarea>
                        @error('motivo')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    @if($camaSeleccionada)
                        <div class="rm-alert rm-alert--info" role="status">
                            Destino: habitación {{ $camaSeleccionada->habitacion->codigo }} · cama {{ $camaSeleccionada->codigo }}.
                            @if($residente->ocupacionActiva) La ocupación actual finalizará y quedará en el historial. @endif
                            La admisión se conserva. La disponibilidad se comprobará nuevamente al confirmar.
                        </div>
                    @elseif($codCama)
                        <p class="rm-alert rm-alert--warning" role="alert">La cama seleccionada ya no está disponible. Elija otra cama; el alojamiento actual se conserva.</p>
                    @endif
                @endif
                @error('residente')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
            </div>
        @endif
        <x-slot:footer>
            <button type="button" wire:click="cerrarModal" class="rm-btn rm-btn--secondary" wire:loading.attr="disabled" wire:target="guardar">Cancelar</button>
            <button type="button" wire:click="guardar" class="rm-btn rm-btn--primary" wire:loading.attr="disabled" wire:target="guardar"
                @disabled($impedimento || !$camaSeleccionada || !$fecha || !$hora)>
                <span wire:loading.remove wire:target="guardar">{{ $residente?->ocupacionActiva ? 'Confirmar traslado' : 'Asignar alojamiento' }}</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </button>
        </x-slot:footer>
    </x-ui.modal-livewire>
</div>
