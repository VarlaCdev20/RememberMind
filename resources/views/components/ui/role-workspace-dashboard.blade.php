@props(['perfil' => [], 'saludo' => []])
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')

@php
    $indicadores = $perfil['indicadores'] ?? [];
    $panels = $perfil['panels'] ?? [];
    $esSuperadministracion = ($perfil['rol'] ?? '') === 'SUPERADMINISTRADOR';
    $nombreSaludo = $saludo['nombre'] ?? 'Usuario';
    if ($esSuperadministracion) {
        $nombreSaludo = mb_convert_case(mb_strtolower($nombreSaludo, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
    $accesos = collect($perfil['accesos'] ?? [])->filter(
        fn ($acceso) => $visibilidadNavegacion->puedeVerRuta($acceso['route'], $acceso['permission'] ?? null)
    );
    $contextoFoto = match ($perfil['rol'] ?? '') {
        'SUPERADMINISTRADOR' => 'superadmin',
        'ADMINISTRADOR' => 'administracion',
        'MEDICO GENERAL/GERIATRA' => 'medico',
        'PSICOLOGO/A' => 'psicologia',
        'NUTRICIONISTA' => 'nutricionista',
        'FISIOTERAPEUTA' => 'fisioterapeuta',
        'PEDAGOGO' => 'pedagogo',
        'FAMILIAR' => 'familiar',
        'ENFERMEROS' => 'enfermeria',
        default => 'perfil-general',
    };
@endphp

<div @class(['rm-dashboard-composition', 'rm-dashboard-composition--superadmin' => $esSuperadministracion])>
    <x-ui.role-dashboard-hero
        personal-greeting
        class="{{ $esSuperadministracion ? 'rm-dashboard-header--superadmin' : '' }}"
        :eyebrow="$perfil['eyebrow'] ?? 'CENTRO GERIÁTRICO LOS ALMENDROS'"
        :title="($saludo['saludo'] ?? 'Bienvenido') . ', ' . $nombreSaludo"
        :highlight="$esSuperadministracion ? null : ($perfil['highlight'] ?? 'Tu espacio de trabajo')"
        :description="$esSuperadministracion ? '' : ($perfil['description'] ?? '')"
        :scope="$esSuperadministracion ? (empty($perfil['jornada']) ? '' : 'Jornada actual: '.$perfil['jornada']) : null"
        :image-label="$esSuperadministracion ? false : null"
        :image="asset($perfil['image'] ?? 'images/FOTOS CENTRO DE ADULTOS MAYORES/489963938_1158744422930145_8442506970304201426_n.jpg')"
        :rotation-context="$contextoFoto"
        :quote="$perfil['quote'] ?? 'Cuidado con propósito'"
        :meta="[
            ['icon' => 'ph-calendar-blank', 'label' => $saludo['fecha'] ?? now()->format('d/m/Y')],
            ['icon' => 'ph-identification-badge', 'label' => $saludo['rolLegible'] ?? 'Perfil institucional'],
            ['icon' => 'ph-shield-check', 'label' => $esSuperadministracion ? '' : 'Acceso según permisos'],
        ]"
    >
        @unless($esSuperadministracion)
            @foreach($accesos->take(3) as $acceso)
                <a href="{{ route($acceso['route']) }}" wire:navigate class="{{ $loop->first ? 'rm-btn-primary' : 'rm-btn-secondary' }} min-h-9 px-3.5 text-xs">
                    <i class="ph-bold {{ $acceso['icon'] }}"></i><span>{{ $acceso['label'] }}</span>
                </a>
            @endforeach
        @endunless
    </x-ui.role-dashboard-hero>

    <x-ui.dashboard-divider />

    @if(!empty($indicadores))
        <section @class(['grid gap-4 sm:grid-cols-2 xl:grid-cols-4', 'rm-superadmin-metrics' => $esSuperadministracion]) aria-label="Indicadores del perfil">
            @foreach($indicadores as $indicador)
                @php($sinAlertas = $esSuperadministracion && ($indicador['label'] ?? '') === 'Alertas prioritarias' && (int) ($indicador['value'] ?? 0) === 0)
                <x-ui.metric-card
                    :icon="$indicador['icon'] ?? 'ph-chart-bar'"
                    :value="$indicador['value'] ?? null"
                    :label="$indicador['label'] ?? 'Indicador'"
                    :description="$sinAlertas ? 'Sin alertas críticas o altas abiertas.' : ($indicador['description'] ?? null)"
                    :variant="$sinAlertas ? 'mint' : ($indicador['variant'] ?? 'neutral')"
                    class="{{ $sinAlertas ? 'rm-superadmin-metric--quiet' : '' }}" />
            @endforeach
        </section>
    @endif

    @if($panels)
        <div class="rm-dashboard-data-grid" aria-label="Información de tu panel">
            @foreach($panels as $panel)
                <x-ui.dashboard-data-panel :panel="$panel" />
            @endforeach
        </div>
    @endif

    @unless($esSuperadministracion)
        <section class="rm-role-access" aria-labelledby="role-access-title">
            <div>
                <p class="rm-dashboard-kicker">NAVEGACIÓN SEGURA</p>
                <h2 id="role-access-title">Accesos disponibles</h2>
                <p>Las opciones se muestran de acuerdo con las competencias y permisos asignados a tu rol.</p>
            </div>
            <div class="rm-role-access__links">
                @forelse($accesos as $acceso)
                    <a href="{{ route($acceso['route']) }}" wire:navigate>
                        <i class="ph-bold {{ $acceso['icon'] }}"></i>
                        <span>{{ $acceso['label'] }}</span>
                        <i class="ph-bold ph-arrow-up-right"></i>
                    </a>
                @empty
                    <span class="rm-role-access__empty">No hay accesos adicionales habilitados para este perfil.</span>
                @endforelse
            </div>
        </section>
    @endunless
</div>
