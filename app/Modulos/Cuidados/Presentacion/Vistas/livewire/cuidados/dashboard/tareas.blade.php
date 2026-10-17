<x-ui.card class="rm-nursing-dashboard__tasks rm-turn-complement" aria-labelledby="nursing-tasks-title">
        <x-ui.section-header title="Tareas del turno" subtitle="Cuidados registrados" icon="ph-list-checks" level="2" id="nursing-tasks-title" />
        <div wire:loading.grid wire:target="refrescarTurno" class="rm-turn-complement__loading" aria-label="Cargando tareas">
            @for($i = 0; $i < 3; $i++)<x-ui.skeleton variant="text" />@endfor
        </div>
        <div wire:loading.remove wire:target="refrescarTurno">
            @if(!$complementos['activo'])
                <x-ui.empty-state compact icono="ph-list-checks" titulo="Sin actividad del turno" texto="Los cuidados registrados aparecerán aquí." />
            @elseif(!$complementos['tareas']['disponible'])
                <x-ui.empty-state compact icono="ph-lock" titulo="Cuidados no disponibles" texto="No tienes acceso a esta información del turno." />
            @elseif(!$complementos['tareas']['total'])
                <x-ui.empty-state compact icono="ph-list-checks" titulo="Sin cuidados registrados" texto="Aún no hay ejecuciones de cuidado en este turno." />
            @else
                <div class="rm-turn-complement__progress-copy"><strong>{{ $complementos['tareas']['completadas'] }}/{{ $complementos['tareas']['total'] }}</strong><span>Realizadas</span></div>
                <progress class="rm-turn-complement__progress" value="{{ $complementos['tareas']['completadas'] }}" max="{{ $complementos['tareas']['total'] }}" aria-label="Tareas realizadas">{{ $complementos['tareas']['progreso'] }}%</progress>
                <ul class="rm-turn-complement__list" aria-label="Cuidados del turno">
                    @foreach($complementos['tareas']['items'] as $tarea)
                        <x-ui.task-item :task="$tarea" :href="auth()->user()?->can('enfermeria.ver_ficha_paciente') && Route::has('admin.enfermeria.pacientes.ficha') ? route('admin.enfermeria.pacientes.ficha', ['adulto' => $tarea['cod_residente'], 'tab' => 'cuidados']) : null" />
                    @endforeach
                </ul>
            @endif
        </div>
</x-ui.card>
