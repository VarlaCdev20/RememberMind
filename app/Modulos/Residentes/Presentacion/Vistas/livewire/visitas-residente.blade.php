<div class="space-y-4">
    @if($mensaje)<p role="status" class="rm-alert rm-alert--success">{{ $mensaje }}</p>@endif
    @error('visita')<p role="alert" class="rm-field-error">{{ $message }}</p>@enderror
    @if(in_array($residente->estado, ['ADMITIDO', 'ACTIVO', 'EST_001'], true))
        @if($contactos->isEmpty())
            <p class="rm-control-help">Vincula un contacto activo al residente para programar visitas.</p>
        @else
            <form wire:submit="programar" class="space-y-3">
                <h3 class="rm-section-title">Programar visita</h3>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="rm-label">Contacto
                        <select class="rm-input w-full" wire:model="contacto" required>
                            <option value="">Seleccionar contacto</option>
                            @foreach($contactos as $persona)<option value="{{ $persona->getKey() }}">{{ $persona->nombres }} {{ $persona->apellido_paterno }}</option>@endforeach
                        </select>
                        @error('contacto')<span class="rm-field-error" role="alert">{{ $message }}</span>@enderror
                        @error('cod_contacto')<span class="rm-field-error" role="alert">{{ $message }}</span>@enderror
                    </label>
                    <label class="rm-label">Fecha y hora programadas
                        <input class="rm-input w-full" type="datetime-local" wire:model="fecha" required>
                        @error('fecha')<span class="rm-field-error" role="alert">{{ $message }}</span>@enderror
                    </label>
                </div>
                <label class="rm-label">Motivo (opcional)<input class="rm-input w-full" wire:model="motivo" maxlength="160"></label>
                @error('motivo')<p class="rm-field-error" role="alert">{{ $message }}</p>@enderror
                <button class="rm-btn-primary" type="submit" wire:loading.attr="disabled">Programar visita</button>
            </form>
        @endif
    @endif
    @forelse($visitas as $visita)
        <article class="rm-card p-3 space-y-2" wire:key="visita-{{ $visita->getKey() }}">
            <div class="flex flex-wrap items-center gap-2"><strong>{{ $visita->contacto->nombres }} {{ $visita->contacto->apellido_paterno }}</strong><x-ui.status-badge :estado="$visita->estado" /></div>
            <p class="rm-body-sm">Programada {{ $visita->fecha_hora_programada?->format('d/m/Y H:i') ?? 'sin fecha' }}
                @if($visita->fecha_hora_ingreso) · Entrada {{ $visita->fecha_hora_ingreso->format('d/m/Y H:i') }}@endif
                @if($visita->fecha_hora_salida) · Salida {{ $visita->fecha_hora_salida->format('d/m/Y H:i') }}@endif
            </p>
            @if($visita->motivo)<p class="rm-body-sm">{{ $visita->motivo }}</p>@endif
            <div class="flex flex-wrap gap-2">
                @if(in_array($visita->estado, ['PROGRAMADA', 'AUTORIZADA'], true) && !$visita->fecha_hora_ingreso && !$visita->fecha_hora_salida)
                    @if(in_array($residente->estado, ['ADMITIDO', 'ACTIVO', 'EST_001'], true))
                        <button type="button" class="rm-btn-primary" wire:click="cambiar(@js($visita->getKey()), 'entrada')" wire:loading.attr="disabled">Registrar entrada</button>
                    @endif
                    <button type="button" class="rm-btn-secondary" wire:click="cambiar(@js($visita->getKey()), 'cancelar')" data-rm-confirm="¿Cancelar esta visita?" wire:loading.attr="disabled">Cancelar visita</button>
                @elseif($visita->estado === 'EN_CURSO' && $visita->fecha_hora_ingreso && !$visita->fecha_hora_salida)
                    <button type="button" class="rm-btn-primary" wire:click="cambiar(@js($visita->getKey()), 'salida')" wire:loading.attr="disabled">Registrar salida</button>
                @endif
            </div>
        </article>
    @empty
        <p class="rm-control-help">Sin visitas registradas.</p>
    @endforelse
    {{ $visitas->links() }}
</div>
