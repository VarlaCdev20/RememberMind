<x-sistema-layout>

    {{-- Zona 1: Encabezado personalizado y estado institucional --}}
    <x-ui.encabezado-dashboard :saludo="$saludo ?? []" />

    {{-- Zona 2: KPIs institucionales macro --}}
    <x-ui.kpis-dashboard :kpis="$kpisInstitucionales ?? []" />

    {{-- Zona 3: Centro de Mando / Hub de Módulos (Todas las Vistas como Botones) --}}
    <x-ui.hub-modulos-dashboard />

    {{-- Zona 4: Vigilancia Clínica y Alertas Prioritarias --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <x-ui.panel-alertas-dashboard :alertas="$alertasEstructuradas ?? []" />
        <x-ui.panel-salud-dashboard :resumen="$resumenSalud ?? []" />
    </div>

    {{-- Zona 5: Operativa Asistencial Diaria y Equipo Institucional --}}
    <div class="grid gap-4 xl:grid-cols-[1.35fr_1fr]">
        <x-ui.panel-actividades-dashboard :actividades="$actividadesDashboard ?? []" />
        <x-ui.panel-equipo-institucional :equipo="$equipoInstitucional ?? []" :redFamiliar="$redFamiliar ?? []" />
    </div>

    {{-- Zona 6: Analítica y Gráficas de Tendencia --}}
    <x-ui.seccion-graficos-dashboard />

    {{-- Zona 7: Trazabilidad y Auditoría Forense Reciente --}}
    <x-ui.tabla-bitacora-dashboard :registros="$bitacoraDashboard ?? []" />

    <script>
const rmDatosc535f32d75d9 = @json($adultosPorEstado ?? ['labels' => [], 'data' => [], 'colores' => []]);
const rmDatos62d1a989ad1d = @json($distribucionEquipoInstitucional ?? ['labels' => [], 'data' => [], 'colores' => []]);
{!! file_get_contents(resource_path('frontend/scripts/modules/pages-dashboard.js')) !!}
</script>

</x-sistema-layout>
