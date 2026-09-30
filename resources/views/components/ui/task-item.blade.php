@props(['task', 'href' => null])
@php
    $done = $task['completed_verified'] ?? false;
    $variant = $done ? 'success' : ($task['status'] === 'EN_PROCESO' ? 'info' : 'neutral');
    $label = match($task['status']) { 'REALIZADA', 'COMPLETADA', 'FINALIZADA' => 'Realizada', 'EN_PROCESO' => 'En proceso', default => ucfirst(mb_strtolower($task['status'])) };
@endphp
<li class="rm-turn-task">
    <i class="ph-bold {{ $done ? 'ph-check-circle' : 'ph-list-checks' }} rm-turn-task__icon" aria-hidden="true"></i>
    <div class="rm-turn-task__body">
        @if($href)<a href="{{ $href }}" class="rm-turn-task__title" aria-label="Consultar cuidados de {{ $task['patient'] }}: {{ $task['title'] }}">{{ $task['title'] }}</a>@else<strong class="rm-turn-task__title">{{ $task['title'] }}</strong>@endif
        <span>{{ $task['patient'] }}</span>
        @if($task['time'])<small>Programada {{ $task['time'] }}</small>@endif
        @if($task['responsible'])<small>{{ $task['responsible'] }}</small>@endif
        <x-ui.status-badge :estado="$task['status']" :label="$label" :variant="$variant" />
        @if(in_array($task['status'], ['REALIZADA', 'COMPLETADA', 'FINALIZADA'], true) && !$done)<small>Sin fecha de ejecución verificable</small>@endif
    </div>
</li>
