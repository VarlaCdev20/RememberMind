<x-sistema-layout>
    @if(($infoRol['es_admin'] ?? false) === true)
        <div class="rm-dashboard-composition">
            <x-ui.encabezado-dashboard :saludo="$saludo ?? []" />
            <x-ui.kpis-dashboard :kpis="$kpisInstitucionales ?? []" />

            <div class="grid gap-4 2xl:grid-cols-[1.15fr_.85fr]">
                <x-ui.seccion-graficos-dashboard />
                <x-ui.panel-salud-dashboard :resumen="$resumenSalud ?? []" />
            </div>

            <div class="grid gap-4 xl:grid-cols-3">
                <x-ui.panel-alertas-dashboard :alertas="$alertasEstructuradas ?? []" />
                <x-ui.panel-actividades-dashboard :actividades="$actividadesDashboard ?? []" />
                <x-ui.panel-equipo-institucional :equipo="$equipoInstitucional ?? []" :redFamiliar="$redFamiliar ?? []" />
            </div>

            <x-ui.hub-modulos-dashboard />
            <x-ui.tabla-bitacora-dashboard :registros="$bitacoraDashboard ?? []" />
        </div>
    @else
        <x-ui.role-workspace-dashboard :perfil="$perfilDashboardRol ?? []" :saludo="$saludo ?? []" />
    @endif

    <script>
const rmDatosc535f32d75d9 = @json($adultosPorEstado ?? ['labels' => [], 'data' => [], 'colores' => []]);
const rmDatos62d1a989ad1d = @json($distribucionEquipoInstitucional ?? ['labels' => [], 'data' => [], 'colores' => []]);
{!! file_get_contents(resource_path('frontend/scripts/modules/pages-dashboard.js')) !!}
</script>

</x-sistema-layout>
