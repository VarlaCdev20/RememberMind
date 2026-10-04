@props(['paciente', 'modoConsulta' => false])
@php
    $nombre = $paciente->nombre_completo;
    $nombreVisible = \Illuminate\Support\Str::title(mb_strtolower($nombre));
    $iniciales = mb_strtoupper(mb_substr((string) $paciente->nombres, 0, 1).mb_substr((string) $paciente->apellido_paterno, 0, 1));
    $habitacion = $paciente->ocupacionActiva?->cama?->habitacion?->codigo;
    $cama = $paciente->ocupacionActiva?->cama?->codigo;
    $ubicacion = $habitacion ? (str_starts_with(mb_strtolower($habitacion), 'hab') ? $habitacion : 'Hab. '.$habitacion).($cama ? ' · '.(str_starts_with(mb_strtolower($cama), 'cama') ? $cama : 'Cama '.$cama) : '') : 'Sin ubicación asignada';
    $alertas = (int) $paciente->alertas_criticas_count;
    $codigo = $paciente->cod_residente;
    $usuario = auth()->user();
    $puedeRegistrarControl = !$modoConsulta && $usuario?->can('signos_vitales.crear');
    $puedeVerCuidados = $usuario?->can('enfermeria.ver_ficha_paciente') && $usuario?->can('planes_cuidado.ver');
    $puedeVerMedicacion = $usuario?->can('enfermeria.ver_ficha_paciente') && ($usuario?->can('prescripciones.ver') || $usuario?->can('administraciones_medicacion.ver'));
    $puedeVerAlertas = $usuario?->can('alertas.ver');
    $tieneAcciones = $puedeRegistrarControl || $puedeVerCuidados || $puedeVerMedicacion || $puedeVerAlertas;
@endphp
<article wire:key="resident-card-{{ $codigo }}" class="rm-resident-directory__item rm-resident-directory__item--card rm-resident-compact-card {{ $this->residente === $codigo ? 'is-selected' : '' }}" role="listitem" x-data="{ open: false }" :class="{ 'has-open-menu': open }">
    <div class="rm-resident-compact-card__identity">
        <button type="button" wire:click="seleccionarResidente('{{ $codigo }}')" class="rm-resident-directory__avatar-button" aria-label="Ver resumen de {{ $nombre }}">
            @if($paciente->foto)
                <img src="{{ asset('storage/'.$paciente->foto) }}" alt="Foto de {{ $nombre }}" loading="lazy" decoding="async" class="rm-resident-directory__avatar">
            @else
                <span class="rm-resident-directory__avatar rm-resident-directory__avatar--initials" aria-hidden="true">{{ $iniciales ?: 'R' }}</span>
            @endif
        </button>
        <button type="button" wire:click="seleccionarResidente('{{ $codigo }}')" class="rm-resident-directory__name">{{ $nombreVisible }}</button>
        <span class="rm-resident-compact-card__age">{{ $paciente->fecha_nacimiento?->age !== null ? $paciente->fecha_nacimiento->age.' años' : 'Edad no registrada' }}</span>
    </div>
    <div class="rm-resident-compact-card__facts">
        <p><i class="ph-bold ph-bed" aria-hidden="true"></i><span>{{ $ubicacion }}</span></p>
        <div class="rm-resident-compact-card__next">
            <span>Próximo cuidado</span>
            <strong>{{ $paciente->proxima_atencion_texto ?: 'Sin acciones programadas' }}</strong>
            @if($paciente->proxima_atencion_hora)<time>{{ $paciente->proxima_atencion_hora }}</time>@endif
        </div>
        <p class="rm-resident-compact-card__alert {{ $alertas > 0 ? 'has-alerts' : '' }}"><i class="ph-bold {{ $alertas > 0 ? 'ph-warning-circle' : 'ph-check-circle' }}" aria-hidden="true"></i><span>{{ $alertas > 0 ? $alertas.' '.($alertas === 1 ? 'alerta prioritaria' : 'alertas prioritarias') : 'Sin alertas prioritarias' }}</span></p>
    </div>
    <div class="rm-resident-compact-card__footer" @keydown.escape.stop="if (open) { open = false; $refs.more.focus() }">
        <button type="button" wire:click="seleccionarResidente('{{ $codigo }}')" class="rm-resident-directory__open" aria-label="Ver resumen de {{ $nombre }}">Ver resumen <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>
        @if($tieneAcciones)
            <button type="button" x-ref="more" class="rm-resident-compact-card__more" :class="{ 'is-open': open }" @click="open = !open; if (open) $nextTick(() => $refs.menu.querySelector('a, button')?.focus())" :aria-expanded="open.toString()" aria-controls="resident-actions-{{ $codigo }}" aria-label="Más acciones para {{ $nombre }}" aria-haspopup="true"><i class="ph-bold ph-dots-three" aria-hidden="true"></i></button>
            <div id="resident-actions-{{ $codigo }}" x-ref="menu" class="rm-resident-compact-card__menu" x-show="open" x-cloak x-transition.opacity.duration.150ms @click.outside="open = false" role="group" aria-label="Acciones para {{ $nombre }}">
                <div class="rm-resident-compact-card__menu-heading">Acciones del residente</div>
                @if($puedeRegistrarControl)<button type="button" wire:click="abrirRegistrarSignos('{{ $codigo }}')" @click="open = false"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-heartbeat" aria-hidden="true"></i></span><span>Registrar control</span></button>@endif
                @if($puedeVerCuidados)<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $codigo, 'tab' => 'cuidados']) }}"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-heart" aria-hidden="true"></i></span><span>Ver cuidados</span></a>@endif
                @if($puedeVerMedicacion)<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $codigo, 'tab' => 'medicacion']) }}"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-pill" aria-hidden="true"></i></span><span>Ver medicación</span></a>@endif
                @if($puedeVerAlertas)<div class="rm-resident-compact-card__menu-divider" aria-hidden="true"></div><a href="{{ route('admin.enfermeria.alertas', ['adulto' => $codigo]) }}"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i></span><span>Ver alertas</span>@if($alertas > 0)<span class="rm-resident-compact-card__menu-count" aria-label="{{ $alertas }} prioritarias">{{ $alertas }}</span>@endif</a>@endif
            </div>
        @endif
    </div>
</article>
