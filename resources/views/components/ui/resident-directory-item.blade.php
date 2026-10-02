@props(['paciente', 'mode' => 'row'])
@php
    $nombre = $paciente->nombre_completo;
    $nombreVisible = \Illuminate\Support\Str::title(mb_strtolower($nombre));
    $iniciales = mb_strtoupper(mb_substr((string) $paciente->nombres, 0, 1).mb_substr((string) $paciente->apellido_paterno, 0, 1));
    $habitacion = $paciente->cama?->habitacion?->codigo ?: $paciente->cama?->habitacion?->nombre;
    $cama = $paciente->cama?->codigo;
    $ubicacion = $habitacion
        ? (str_starts_with(mb_strtolower($habitacion), 'hab') ? $habitacion : 'Hab. '.$habitacion)
            .($cama ? ' · '.(str_starts_with(mb_strtolower($cama), 'cama') ? $cama : 'Cama '.$cama) : '')
        : 'Sin ubicación asignada';
    $edad = $paciente->fecha_nacimiento?->age;
    $proximo = $paciente->proxima_atencion_texto;
    $hora = $paciente->proxima_atencion_hora;
@endphp
<article wire:key="resident-{{ $mode }}-{{ $paciente->cod_residente }}" class="rm-resident-directory__item rm-resident-directory__item--{{ $mode }} {{ $this->residente === $paciente->cod_residente ? 'is-selected' : '' }}" role="listitem">
    <span class="rm-resident-directory__status rm-resident-directory__status--{{ $paciente->estado_color }}"><i class="ph-bold {{ $paciente->estado_color === 'red' ? 'ph-warning-circle' : ($paciente->estado_color === 'amber' ? 'ph-heartbeat' : 'ph-shield-check') }}" aria-hidden="true"></i>{{ $paciente->estado_label }}</span>
    <div class="rm-resident-directory__identity">
        <button type="button" wire:click="seleccionarResidente('{{ $paciente->cod_residente }}')" class="rm-resident-directory__avatar-button" aria-label="Ver resumen de {{ $nombre }}">
            <span class="rm-resident-directory__avatar-ornament" aria-hidden="true"><i class="ph ph-leaf"></i><i class="ph ph-leaf"></i></span>
            @if($paciente->foto)
                <img src="{{ asset('storage/'.$paciente->foto) }}" alt="Foto de {{ $nombre }}" loading="lazy" decoding="async" class="rm-resident-directory__avatar">
            @else
                <span class="rm-resident-directory__avatar rm-resident-directory__avatar--initials" aria-hidden="true">{{ $iniciales ?: 'R' }}</span>
            @endif
        </button>
        <div class="rm-resident-directory__name-wrap">
            <button type="button" wire:click="seleccionarResidente('{{ $paciente->cod_residente }}')" class="rm-resident-directory__name">{{ $nombreVisible }}</button>
            <span><i class="ph-bold ph-user" aria-hidden="true"></i>{{ $edad !== null ? $edad.' años' : 'Edad no registrada' }}</span>
        </div>
    </div>
    <div class="rm-resident-directory__meta">
        <div class="rm-resident-directory__location"><i class="ph-bold ph-bed" aria-hidden="true"></i><span><small>Ubicación</small>{{ $ubicacion }}</span></div>
        <div class="rm-resident-directory__next"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><span><small>Próximo cuidado</small>{{ $proximo ?: 'Sin cuidado programado' }}@if($hora)<em>{{ $hora }}</em>@endif</span></div>
    </div>
    <button type="button" wire:click="seleccionarResidente('{{ $paciente->cod_residente }}')" class="rm-resident-directory__open" aria-label="Ver resumen de {{ $nombre }}">Ver resumen <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></button>
</article>
