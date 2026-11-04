<x-ui.residents-page-header :title="$cuidadoActual ? 'Cuidados · '.$cuidadoActual['label'] : ($esSuperAdmin ? 'Supervisión de residentes' : 'MIS RESIDENTES')" :subtitle="$cuidadoActual ? 'Selecciona un residente para consultar o registrar este cuidado.' : ($esModoConsulta ? 'Tus residentes asignados · Consulta fuera de turno, sin registro clínico.' : 'Personas asignadas a tu cuidado en esta jornada.')" :turno="$turnoActual" :modo-consulta="$esModoConsulta">
    <x-slot:actions>
        @can('ejecuciones_cuidado.ver')
            <a href="{{ route('admin.enfermeria.tareas') }}" class="rm-btn rm-btn-secondary"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><span>Cuidados programados</span></a>
        @endcan
    </x-slot:actions>
</x-ui.residents-page-header>
