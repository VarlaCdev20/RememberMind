@props(['kpis' => []])

@php
$estilosColor = [
    'azul-profundo' => ['bg' => 'bg-[var(--rm-info-soft)] text-[var(--rm-info)]', 'val' => 'text-[var(--rm-info)]'],
    'naranja' => ['bg' => 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning)]', 'val' => 'text-[var(--rm-warning)]'],
    'verde-salud' => ['bg' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)]', 'val' => 'text-[var(--rm-success)]'],
    'morado-cog' => ['bg' => 'bg-[var(--rm-mocha)]/20 text-[var(--rm-mocha)]', 'val' => 'text-[var(--rm-mocha)]'],
    'terracota' => ['bg' => 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)]', 'val' => 'text-[var(--rm-action-primary)]'],
    'verde-olivo' => ['bg' => 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)]', 'val' => 'text-[var(--rm-action-primary)]'],
];

$badgeNivel = [
    'normal' => 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] border border-[var(--rm-border)]',
    'ok' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success)]/40',
    'alerta' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40',
    'advertencia' => 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border border-[var(--rm-warning)]/40',
];
@endphp

<div class="rm-dashboard-kpi-grid">
    @foreach($kpis as $kpi)
        @php
            $estilo = $estilosColor[$kpi['color']] ?? $estilosColor['azul-profundo'];
            $badgeClass = $badgeNivel[$kpi['nivel']] ?? $badgeNivel['normal'];
        @endphp

        <div class="min-w-0 h-[116px] min-h-[108px] max-h-[120px] rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3 shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between">
            <div class="mb-1.5 flex min-w-0 items-start justify-between gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-xl {{ $estilo['bg'] }}">
                    <i class="ph-bold {{ $kpi['icono'] }} text-lg"></i>
                </div>
                @if(($kpi['nivel'] ?? 'normal') !== 'normal')
                    <span class="max-w-[8rem] rounded-lg {{ $badgeClass }} px-2 py-0.5 text-center text-[10px] font-bold uppercase leading-tight tracking-wider">
                        {{ $kpi['badge'] }}
                    </span>
                @endif
            </div>

            <div>
                <p class="text-[30px] font-extrabold tracking-tight leading-none {{ $estilo['val'] }}">{{ $kpi['valor'] }}</p>
                <p class="mt-0.5 text-[11px] font-bold leading-tight text-[var(--rm-text-primary)]">{{ $kpi['titulo'] }}</p>
                <p class="mt-0.5 text-[10px] font-semibold leading-tight text-[var(--rm-text-muted)]">{{ $kpi['subtitulo'] }}</p>
            </div>
        </div>
    @endforeach
</div>
