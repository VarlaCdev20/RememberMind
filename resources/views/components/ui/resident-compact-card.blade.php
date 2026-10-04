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
@endphp
<article wire:key="resident-card-{{ $codigo }}" class="rm-resident-directory__item rm-resident-directory__item--card rm-resident-compact-card {{ $this->residente === $codigo ? 'is-selected' : '' }}" role="listitem">
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
    <div class="rm-resident-compact-card__footer" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button" wire:click="seleccionarResidente('{{ $codigo }}')" class="rm-resident-directory__open" aria-label="Ver resumen de {{ $nombre }}">Ver resumen <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>
        <button type="button" class="rm-resident-compact-card__more" @click="open = !open" :aria-expanded="open.toString()" aria-label="Más acciones para {{ $nombre }}" aria-haspopup="menu"><i class="ph-bold ph-dots-three" aria-hidden="true"></i></button>
        <div class="rm-resident-compact-card__menu" x-show="open" x-cloak @click.outside="open = false" role="menu">
            @if(!$modoConsulta && auth()->user()?->can('signos_vitales.crear'))<button type="button" role="menuitem" wire:click="abrirRegistrarSignos('{{ $codigo }}')" @click="open = false">Registrar control</button>@endif
            @if(auth()->user()?->can('enfermeria.ver_ficha_paciente') && auth()->user()?->can('planes_cuidado.ver'))<a role="menuitem" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $codigo, 'tab' => 'cuidados']) }}">Ver cuidados</a>@endif
            @if(auth()->user()?->can('enfermeria.ver_ficha_paciente') && (auth()->user()?->can('prescripciones.ver') || auth()->user()?->can('administraciones_medicacion.ver')))<a role="menuitem" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $codigo, 'tab' => 'medicacion']) }}">Ver medicación</a>@endif
            @can('alertas.ver')<div class="rm-resident-compact-card__menu-divider"></div><a role="menuitem" href="{{ route('admin.enfermeria.alertas', ['adulto' => $codigo]) }}">Ver alertas</a>@endcan
        </div>
    </div>
</article>
