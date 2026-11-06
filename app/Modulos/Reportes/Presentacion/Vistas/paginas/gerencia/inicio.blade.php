@inject('visibilidadGerencia', 'App\Modulos\Identidad\Presentacion\Navegacion\VisibilidadNavegacion')
@php
    $reporte = $perfil['gerencia'];
    $rutas = ['personal'=>'admin.personal-institucional', 'documentacion'=>'admin.personal.documentacion', 'ocupacion'=>'admin.habitaciones.index', 'alertas'=>'admin.alertas-clinicas.index'];
    $enlaces = collect($rutas)->map(fn ($ruta) => $visibilidadGerencia->puedeVerRuta($ruta) ? route($ruta) : null)->all();
@endphp
<div class="rm-management-workspace rm-manager-home rm-dashboard-composition font-sans">
    <x-ui.dashboard-header class="rm-manager-home__welcome" :title="($saludo['saludo'] ?? 'Bienvenido').', '.mb_convert_case(mb_strtolower($saludo['nombre'] ?? 'Gerencia'), MB_CASE_TITLE, 'UTF-8')" subtitle="Resumen de hoy" role="Gerencia" role-position="corner" scope="" :date="now()->locale('es')->translatedFormat('d \\d\\e F \\d\\e Y')" :image="asset('images/dashboard/enfermera-manos.webp')" />
    @include('reportes::paginas.gerencia.partials.indicadores', ['compacto' => true])
    <x-ui.section-card class="rm-manager-home__pending" title="Para revisar hoy" icon="ph-clipboard-text">
        <div class="rm-management-pending">
            @if($enlaces['alertas'])<a href="{{ $enlaces['alertas'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-user-circle-dashed" aria-hidden="true"></i><span><strong>{{ $reporte['sin_responsable'] }} alertas sin responsable</strong></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a><a href="{{ $enlaces['alertas'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-clock" aria-hidden="true"></i><span><strong>{{ $reporte['limite_superado'] }} alertas fuera de plazo</strong></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
            @if($enlaces['documentacion'])<a href="{{ $enlaces['documentacion'] }}" class="rm-management-pending-item" wire:navigate><i class="ph-bold ph-folder-user" aria-hidden="true"></i><span><strong>Documentación del personal</strong></span><i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
        </div>
    </x-ui.section-card>
    <x-ui.section-card class="rm-manager-home__planning" title="Organizar el equipo" icon="ph-calendar-check">
        <nav class="rm-management-tabs" aria-label="Organización de Gerencia">@foreach($accesos as $acceso)<a class="rm-btn-secondary" href="{{ route($acceso['route']) }}" wire:navigate><i class="ph-bold {{ $acceso['icon'] }}" aria-hidden="true"></i>{{ $acceso['label'] }}</a>@endforeach</nav>
    </x-ui.section-card>
    @include('reportes::paginas.gerencia.partials.distribuciones')
    <details class="rm-management-secondary"><summary>Equipo y asignaciones de hoy</summary><div class="rm-management-panels">@foreach($panels as $panel)<x-ui.dashboard-data-panel :panel="$panel" />@endforeach</div></details>
    <details class="rm-management-secondary"><summary>Sobre estos datos · {{ \Carbon\Carbon::parse($reporte['consultado_en'])->format('d/m/Y H:i') }}</summary><ul class="rm-management-limits"><li>Personal activo se refiere al estado laboral, no al acceso a la cuenta.</li><li>Las alertas se cuentan sin duplicar eventos; los plazos son los registrados.</li><li>Las asignaciones no acreditan presencia física ni suficiencia de dotación.</li><li>Una fuente no autorizada se muestra como no disponible.</li></ul></details>
</div>
