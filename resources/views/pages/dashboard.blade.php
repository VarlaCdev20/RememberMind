<x-sistema-layout>

    {{-- Zona 1: Encabezado personalizado --}}
    <x-ui.encabezado-dashboard :saludo="$saludo ?? []" />

    {{-- Zona 2: KPIs institucionales --}}
    <x-ui.kpis-dashboard :kpis="$kpisInstitucionales ?? []" />

    {{-- Zona 3: Resumen de salud + Equipo institucional --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <x-ui.panel-salud-dashboard :resumen="$resumenSalud ?? []" />
        <x-ui.panel-equipo-institucional :equipo="$equipoInstitucional ?? []" :redFamiliar="$redFamiliar ?? []" />
    </div>

    {{-- Zona 4: Gráficas --}}
    <x-ui.seccion-graficos-dashboard />

    {{-- Zona 5: Alertas + Actividades --}}
    <div class="grid gap-4 xl:grid-cols-[1fr_1.35fr]">
        <x-ui.panel-alertas-dashboard :alertas="$alertasEstructuradas ?? []" />
        <x-ui.panel-actividades-dashboard :actividades="$actividadesDashboard ?? []" />
    </div>

    {{-- Zona 6: Bitácora --}}
    <x-ui.tabla-bitacora-dashboard :registros="$bitacoraDashboard ?? []" />

    <script>
const rmDatosc535f32d75d9 = @json($adultosPorEstado ?? ['labels' => [], 'data' => [], 'colores' => []]);
const rmDatos62d1a989ad1d = @json($distribucionEquipoInstitucional ?? ['labels' => [], 'data' => [], 'colores' => []]);
{!! file_get_contents(resource_path('frontend/scripts/modules/pages-dashboard.js')) !!}
</script>

</x-sistema-layout>