@inject('visibilidadGerencia', 'App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion')
@php
    $reporte = $perfil['gerencia'];
    $rutas = ['personal'=>'admin.personal-institucional', 'ocupacion'=>'admin.habitaciones.index', 'alertas'=>'admin.alertas-clinicas.index'];
    $enlaces = collect($rutas)->map(fn ($ruta) => $visibilidadGerencia->puedeVerRuta($ruta) ? route($ruta) : null)->all();
@endphp
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header :title="($saludo['saludo'] ?? 'Bienvenido').', '.mb_convert_case(mb_strtolower($saludo['nombre'] ?? 'Gerencia'), MB_CASE_TITLE, 'UTF-8')" subtitle="Resumen institucional de personal, ocupación y alertas" icon="ph-buildings" eyebrow="Inicio de Gerencia" :date="now()->locale('es')">
        <x-slot:actions>@if($visibilidadGerencia->puedeVerRuta('admin.reportes.institucional.preview'))<a class="rm-btn-secondary" href="{{ route('admin.reportes.institucional.preview') }}" wire:navigate><i class="ph-bold ph-chart-bar" aria-hidden="true"></i> Ver reportes</a>@endif</x-slot:actions>
    </x-ui.collection-header>
    @include('reportes::paginas.gerencia.partials.indicadores')
    <x-ui.section-card title="Pendientes de Gerencia" subtitle="Información registrada que necesita revisión; no sustituye el seguimiento profesional." icon="ph-clipboard-text">
        <div class="rm-management-pending">
            @if($enlaces['alertas'])<a href="{{ $enlaces['alertas'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-user-circle-dashed" aria-hidden="true"></i><span><strong>{{ $reporte['sin_responsable'] }} sin responsable</strong><small>Alertas activas sin asignación registrada</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a><a href="{{ $enlaces['alertas'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-clock" aria-hidden="true"></i><span><strong>{{ $reporte['limite_superado'] }} con límite superado</strong><small>Según la fecha límite registrada</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
            @if($enlaces['personal'])<a href="{{ $enlaces['personal'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-folder-user" aria-hidden="true"></i><span><strong>Documentación del personal</strong><small>Consultar entregas y estado de los documentos</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
        </div>
    </x-ui.section-card>
    @include('reportes::paginas.gerencia.partials.distribuciones')
    <x-ui.section-card title="Áreas y planificación" subtitle="Asignaciones registradas; no acreditan presencia física ni suficiencia de dotación." icon="ph-calendar-check">
        <nav class="rm-management-tabs" aria-label="Organización de Gerencia">@foreach($accesos as $acceso)<a class="rm-btn-secondary" href="{{ route($acceso['route']) }}" wire:navigate><i class="ph-bold {{ $acceso['icon'] }}" aria-hidden="true"></i>{{ $acceso['label'] }}</a>@endforeach</nav>
    </x-ui.section-card>
    <details class="rm-management-secondary"><summary>Equipo y asignaciones de hoy</summary><div class="rm-management-panels">@foreach($panels as $panel)<x-ui.dashboard-data-panel :panel="$panel" />@endforeach</div></details>
    <p class="rm-help">Datos consultados el {{ \Carbon\Carbon::parse($reporte['consultado_en'])->format('d/m/Y H:i') }}. Una fuente no autorizada se muestra como no disponible.</p>
</div>
