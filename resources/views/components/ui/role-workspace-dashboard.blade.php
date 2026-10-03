@props(['perfil' => [], 'saludo' => []])
@inject('visibilidadNavegacion', 'App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion')

@php
    $indicadores = $perfil['indicadores'] ?? [];
    $panels = $perfil['panels'] ?? [];
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

<div class="rm-dashboard-composition">
    <x-ui.role-dashboard-hero
        personal-greeting
        :eyebrow="$perfil['eyebrow'] ?? 'CENTRO GERIÁTRICO LOS ALMENDROS'"
        :title="($saludo['saludo'] ?? 'Bienvenido') . ', ' . ($saludo['nombre'] ?? 'Usuario')"
        :highlight="$perfil['highlight'] ?? 'Tu espacio de trabajo'"
        :description="$perfil['description'] ?? ''"
        :image="asset($perfil['image'] ?? 'images/FOTOS CENTRO DE ADULTOS MAYORES/489963938_1158744422930145_8442506970304201426_n.jpg')"
        :rotation-context="$contextoFoto"
        :quote="$perfil['quote'] ?? 'Cuidado con propósito'"
        :meta="[
            ['icon' => 'ph-calendar-blank', 'label' => $saludo['fecha'] ?? now()->format('d/m/Y')],
            ['icon' => 'ph-identification-badge', 'label' => $saludo['rolLegible'] ?? 'Perfil institucional'],
            ['icon' => 'ph-shield-check', 'label' => 'Acceso según permisos'],
        ]"
    >
        @foreach($accesos->take(3) as $acceso)
            <a href="{{ route($acceso['route']) }}" wire:navigate class="{{ $loop->first ? 'rm-btn-primary' : 'rm-btn-secondary' }} min-h-9 px-3.5 text-xs">
                <i class="ph-bold {{ $acceso['icon'] }}"></i><span>{{ $acceso['label'] }}</span>
            </a>
        @endforeach
    </x-ui.role-dashboard-hero>

    @if(!empty($indicadores))
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores del perfil">
            @foreach($indicadores as $indicador)
                <x-ui.metric-card
                    :icon="$indicador['icon'] ?? 'ph-chart-bar'"
                    :value="$indicador['value'] ?? null"
                    :label="$indicador['label'] ?? 'Indicador'"
                    :description="$indicador['description'] ?? null"
                    :variant="$indicador['variant'] ?? 'neutral'" />
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
</div>
