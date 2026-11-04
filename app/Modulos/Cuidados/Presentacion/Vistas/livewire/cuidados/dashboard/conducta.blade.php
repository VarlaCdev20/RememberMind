<x-ui.card class="rm-nursing-dashboard__notes rm-turn-complement" aria-labelledby="nursing-notes-title">
    <x-ui.section-header title="Conducta y estado emocional" subtitle="Últimas 24 horas" icon="ph-note" level="2" id="nursing-notes-title" />
    <div wire:loading.grid wire:target="refrescarTurno" class="rm-turn-complement__loading" aria-label="Cargando observaciones">
        @for($i = 0; $i < 3; $i++)<x-ui.skeleton variant="text" />@endfor
    </div>
    <div wire:loading.remove wire:target="refrescarTurno">
        @if(!$complementos['activo'])
            <x-ui.empty-state compact icono="ph-note" titulo="Sin observaciones del turno" texto="Los registros de conducta aparecerán aquí." />
        @elseif(!$complementos['notas']['disponible'])
            <x-ui.empty-state compact icono="ph-note" titulo="Conducta no disponible" texto="No se puede consultar esta información ahora." />
        @elseif(empty($complementos['notas']['items']))
            <x-ui.empty-state compact icono="ph-note" titulo="Sin observaciones recientes" texto="No hay registros de las últimas 24 horas." />
        @else
            <ul class="rm-turn-complement__list" aria-label="Observaciones de conducta recientes">
                @foreach($complementos['notas']['items'] as $nota)
                    <x-ui.behavior-note :note="$nota" :href="auth()->user()?->can('enfermeria.ver_ficha_paciente') && Route::has('admin.enfermeria.pacientes.ficha') ? route('admin.enfermeria.pacientes.ficha', ['adulto' => $nota['cod_residente'], 'tab' => 'seguimiento']) : null" />
                @endforeach
            </ul>
        @endif
    </div>
</x-ui.card>
