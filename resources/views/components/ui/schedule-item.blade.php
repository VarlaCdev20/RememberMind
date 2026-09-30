@props(['time', 'title', 'patient' => null, 'type' => 'CUIDADO', 'status' => 'PENDIENTE', 'icon' => 'ph-stethoscope', 'omission' => null, 'datetime' => null])
@php
    $resolved = match ($status) {
        'RETRASADO' => ['label' => 'Retrasado', 'variant' => 'danger'],
        'PRÓXIMO' => ['label' => 'Próximo', 'variant' => 'info'],
        'REALIZADO' => ['label' => $omission ? 'Omitido registrado' : 'Realizado', 'variant' => $omission ? 'neutral' : 'success'],
        'PENDIENTE' => ['label' => 'Pendiente', 'variant' => 'neutral'],
        default => ['label' => ucfirst(mb_strtolower(str_replace('_', ' ', $status))), 'variant' => 'neutral'],
    };
@endphp
<li {{ $attributes->class(['rm-schedule-item', 'rm-schedule-item--done' => $status === 'REALIZADO']) }}>
    <time class="rm-schedule-item__time" @if($datetime) datetime="{{ $datetime }}" @endif>{{ $time }}</time>
    <span class="rm-schedule-item__marker" aria-hidden="true"><i class="ph-bold {{ $status === 'REALIZADO' && !$omission ? 'ph-check' : ($type === 'MEDICACION' ? 'ph-pill' : $icon) }}"></i></span>
    <div class="rm-schedule-item__body">
        <strong class="rm-schedule-item__title" title="{{ $title }}">{{ $title }}</strong>
        @if($patient)<span class="rm-schedule-item__patient">{{ $patient }}</span>@endif
    </div>
    <x-ui.status-badge :estado="$status" :label="$resolved['label']" :variant="$resolved['variant']" class="rm-schedule-item__status" />
</li>
