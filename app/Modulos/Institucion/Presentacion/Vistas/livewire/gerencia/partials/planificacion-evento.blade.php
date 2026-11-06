@php($codigoEvento = $evento['asignacion']->cod_asignacion)
<button type="button" class="rm-planning-event {{ $asignacionSeleccionada === $codigoEvento ? 'is-selected' : '' }}"
        wire:key="{{ $origen }}-{{ $codigoEvento }}" data-asignacion="{{ $codigoEvento }}"
        wire:click="seleccionarAsignacion('{{ $codigoEvento }}')" wire:loading.attr="disabled"
        title="{{ $evento['titulo'] }} · {{ $evento['rol']['label'] }} · {{ $evento['hora'] }}"
        aria-pressed="{{ $asignacionSeleccionada === $codigoEvento ? 'true' : 'false' }}"
        aria-label="{{ $evento['titulo'] }}, {{ $evento['rol']['label'] }}, {{ $evento['subtitulo'] }}, {{ $evento['hora'] }}">
    <strong>{{ $evento['titulo'] }}</strong>
    <span>{{ $evento['rol']['label'] }}</span>
    <span class="rm-planning-event__time"><i class="ph-bold ph-clock" aria-hidden="true"></i>{{ $evento['inicio_hora'] }}–{{ $evento['fin_hora'] }}</span>
    @if($evento['cruza_dia'])<small>+1 día · {{ $evento['hora'] }}</small>@endif
    @if($evento['estado'] === 'CONFLICTO')<small class="rm-planning-event__notice"><i class="ph-bold ph-warning" aria-hidden="true"></i> Solapamiento registrado</small>@elseif($evento['estado'] === 'SUSPENDIDO')<small class="rm-planning-event__notice">Revisar estado del trabajador</small>@endif
</button>
