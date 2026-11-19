<x-ui.residents-page-header :title="$cuidadoActual ? 'Cuidados · '.$cuidadoActual['label'] : ($esSuperAdmin ? 'Supervisión de residentes' : 'MIS RESIDENTES')" :subtitle="$cuidadoActual ? 'Selecciona un residente.' : ($esSuperAdmin ? 'Residentes disponibles para supervisión.' : 'Residentes asignados a tu cuidado.')" :turno="$turnoActual" :modo-consulta="$esModoConsulta">
    <x-slot:actions>
        <x-ui.nursing-report-link :area="$cuidado" />
        @can('ejecuciones_cuidado.ver')
            <a href="{{ route('admin.enfermeria.tareas') }}" class="rm-btn rm-btn-secondary" data-rm-action-icon aria-label="Cuidados programados"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><span>Cuidados programados</span></a>
        @endcan
    </x-slot:actions>
</x-ui.residents-page-header>
