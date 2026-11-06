@inject('visibilidadGerencia', 'App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion')
@php
    $reporte = $perfil['gerencia'];
    $rutas = ['personal'=>'admin.personal-institucional', 'documentacion'=>'admin.personal.documentacion', 'ocupacion'=>'admin.habitaciones.index', 'alertas'=>'admin.alertas-clinicas.index'];
    $enlaces = collect($rutas)->map(fn ($ruta) => $visibilidadGerencia->puedeVerRuta($ruta) ? route($ruta) : null)->all();
@endphp
<div class="rm-management-workspace rm-manager-home">
    <x-ui.dashboard-header class="rm-manager-home__welcome" :title="($saludo['saludo'] ?? 'Bienvenido').', '.mb_convert_case(mb_strtolower($saludo['nombre'] ?? 'Gerencia'), MB_CASE_TITLE, 'UTF-8')" subtitle="Una mirada al equipo, la residencia y los pendientes de hoy." role="Gerencia" scope="Dirección y planificación institucional" :date="now()->locale('es')->translatedFormat('d \\d\\e F \\d\\e Y')" :image="asset('images/dashboard/enfermera-manos.webp')" />
    @include('reportes::paginas.gerencia.partials.indicadores')
    <x-ui.section-card class="rm-manager-home__pending" title="Para revisar hoy" subtitle="Pendientes registrados para seguimiento institucional" icon="ph-clipboard-text">
        <div class="rm-management-pending">
            @if($enlaces['alertas'])<a href="{{ $enlaces['alertas'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-user-circle-dashed" aria-hidden="true"></i><span><strong>{{ $reporte['sin_responsable'] }} sin responsable</strong><small>Alertas activas sin asignación registrada</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a><a href="{{ $enlaces['alertas'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-clock" aria-hidden="true"></i><span><strong>{{ $reporte['limite_superado'] }} con límite superado</strong><small>Según la fecha límite registrada</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
            @if($enlaces['documentacion'])<a href="{{ $enlaces['documentacion'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-folder-user" aria-hidden="true"></i><span><strong>Documentación del personal</strong><small>Consultar archivos y entregas</small></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
        </div>
    </x-ui.section-card>
    <x-ui.section-card class="rm-manager-home__planning" title="Organizar el equipo" subtitle="Áreas, personal y planificación" icon="ph-calendar-check">
        <nav class="rm-management-tabs" aria-label="Organización de Gerencia">@foreach($accesos as $acceso)<a class="rm-btn-secondary" href="{{ route($acceso['route']) }}" wire:navigate><i class="ph-bold {{ $acceso['icon'] }}" aria-hidden="true"></i>{{ $acceso['label'] }}</a>@endforeach</nav>
        <p class="rm-help">Las asignaciones no acreditan presencia física ni suficiencia de dotación.</p>
    </x-ui.section-card>
    @include('reportes::paginas.gerencia.partials.distribuciones')
    <details class="rm-management-secondary"><summary>Equipo y asignaciones de hoy</summary><div class="rm-management-panels">@foreach($panels as $panel)<x-ui.dashboard-data-panel :panel="$panel" />@endforeach</div></details>
    <p class="rm-help">Datos consultados el {{ \Carbon\Carbon::parse($reporte['consultado_en'])->format('d/m/Y H:i') }}. Una fuente no autorizada se muestra como no disponible.</p>
</div>
