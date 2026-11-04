<x-ui.card class="rm-nursing-dashboard__schedule rm-nursing-module rm-nursing-agenda" aria-labelledby="nursing-schedule-title">
    <x-ui.section-header title="Agenda de medicación y cuidados" :subtitle="($dashboard['estado'] ?? null) === 'SIN_JORNADA_ACTIVA' ? 'Programación de Enfermería' : (($dashboard['modo'] ?? '') === 'FUERA_DE_TURNO' ? 'Mis residentes asignados · solo lectura' : 'Programación de tu turno')" icon="ph-calendar-check" level="2" id="nursing-schedule-title">
        @if(Route::has('admin.enfermeria.agenda'))
            <x-slot:actions>
                <a class="rm-nursing-module__more" href="{{ route('admin.enfermeria.agenda') }}">Ver agenda <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>
            </x-slot:actions>
        @endif
    </x-ui.section-header>
    <div wire:loading.grid wire:target="refrescarTurno" class="rm-nursing-module__loading" aria-label="Cargando agenda">
        @for($i = 0; $i < 4; $i++)<x-ui.skeleton class="rm-schedule-item__skeleton" />@endfor
    </div>
    <div wire:loading.remove wire:target="refrescarTurno" class="rm-nursing-agenda__content">
        @if(($dashboard['estado'] ?? null) === 'SIN_JORNADA_ACTIVA')
            <x-ui.empty-state compact icono="ph-calendar-x" titulo="Sin eventos del turno" texto="La agenda aparecerá cuando comience una jornada." />
        @elseif(empty($dashboard['agenda_resumen']))
            <x-ui.empty-state compact icono="ph-calendar-check" titulo="Sin eventos programados" texto="No hay cuidados o medicaciones programadas para este turno." />
        @else
            <ol class="rm-nursing-agenda__timeline" aria-label="Próximos eventos del turno">
                @foreach($dashboard['agenda_resumen'] as $evento)
                    <x-ui.schedule-item :time="$evento['time']" :datetime="$evento['datetime']" :title="$evento['title']" :patient="$evento['patient']" :type="$evento['type']" :status="$evento['status']" :icon="$evento['icon']" :omission="$evento['omission']" />
                @endforeach
            </ol>
        @endif
    </div>
</x-ui.card>
