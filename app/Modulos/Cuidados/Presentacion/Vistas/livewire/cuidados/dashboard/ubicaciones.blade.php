<x-ui.card class="rm-nursing-dashboard__location rm-turn-complement" aria-labelledby="nursing-location-title">
    <x-ui.section-header title="Ubicación de residentes" subtitle="Habitaciones y camas" icon="ph-bed" level="2" id="nursing-location-title" />
    <div wire:loading.grid wire:target="refrescarTurno" class="rm-turn-complement__loading" aria-label="Cargando ubicaciones">
        @for($i = 0; $i < 4; $i++)<x-ui.skeleton variant="text" />@endfor
    </div>
    <div wire:loading.remove wire:target="refrescarTurno">
        @if(!$complementos['activo'] || !$complementos['ubicacion']['total'])
            <x-ui.empty-state compact icono="ph-bed" titulo="Sin ubicaciones del turno" texto="No hay residentes asignados en este contexto." />
        @elseif(!$complementos['ubicacion']['disponible'])
            <x-ui.empty-state compact icono="ph-bed" titulo="Ubicación no disponible" texto="No se pudo consultar la ocupación del turno." />
        @elseif(empty($complementos['ubicacion']['items']))
            <x-ui.empty-state compact icono="ph-bed" titulo="Ubicación no disponible" texto="Sin ocupaciones activas verificables en este contexto." />
        @else
            <div class="rm-turn-complement__locations">
                @foreach($complementos['ubicacion']['items'] as $ubicacion)
                    <x-ui.location-cell :location="$ubicacion" :href="auth()->user()?->can('enfermeria.ver_ficha_paciente') && Route::has('admin.enfermeria.pacientes.ficha') ? route('admin.enfermeria.pacientes.ficha', ['adulto' => $ubicacion['cod_residente']]) : null" />
                @endforeach
            </div>
            @if($complementos['ubicacion']['sin_ubicacion'])<p class="rm-turn-complement__hint">{{ $complementos['ubicacion']['sin_ubicacion'] }} sin ubicación verificable.</p>@endif
            @if(($complementos['ubicacion']['ubicados'] ?? 0) > 6)<p class="rm-turn-complement__hint">Mostrando 6 de {{ $complementos['ubicacion']['ubicados'] }} ubicaciones.</p>@endif
        @endif
    </div>
</x-ui.card>
