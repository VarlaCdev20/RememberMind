{{--
    Componente: ui/metric-card
    Uso: <x-ui.metric-card etiqueta="Total Usuarios" :valor="$total" icono="ph-users" variant="primary" />
    Soporta variantes semánticas: neutral, primary, success, info, danger, warning.
    Preserva retrocompatibilidad para clases custom vía atributos.
--}}
@props([
    'etiqueta' => '',
    'valor' => '—',
    'icono' => null,
    'variant' => 'neutral',
    'colorValor' => null,
    'colorFondo' => null,
    'colorBorde' => null,
])

@php
    $variantValueColor = match($variant) {
        'clinical' => 'text-[var(--rm-clinical)]',
        'primary' => 'text-[var(--rm-action-primary)]',
        'success' => 'text-[var(--rm-success)]',
        'info' => 'text-[var(--rm-info)]',
        'warning' => 'text-[var(--rm-warning)]',
        'danger' => 'text-[var(--rm-danger)]',
        default => 'text-[var(--rm-text-primary)]',
    };

    $variantIconStyle = match($variant) {
        'clinical' => 'bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical)] border-[var(--rm-clinical)]/25',
        'primary' => 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border-[var(--rm-action-primary)]/25',
        'success' => 'bg-[var(--rm-success-soft)] text-[var(--rm-success)] border-[var(--rm-success)]/25',
        'info' => 'bg-[var(--rm-info-soft)] text-[var(--rm-info)] border-[var(--rm-info)]/25',
        'warning' => 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border-[var(--rm-warning)]/25',
        'danger' => 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border-[var(--rm-danger)]/25',
        default => 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-secondary)] border-[var(--rm-border-soft)]',
    };

    $variantBorderAccent = match($variant) {
        'clinical' => 'border-l-[3px] border-l-[var(--rm-clinical)]',
        'primary' => 'border-l-[3px] border-l-[var(--rm-action-primary)]',
        'success' => 'border-l-[3px] border-l-[var(--rm-success)]',
        'info' => 'border-l-[3px] border-l-[var(--rm-info)]',
        'warning' => 'border-l-[3px] border-l-[var(--rm-warning)]',
        'danger' => 'border-l-[3px] border-l-[var(--rm-danger)]',
        default => '',
    };

    $resolvedValueColor = $colorValor ?? $variantValueColor;
    $resolvedBgColor = $colorFondo ?? 'bg-[var(--rm-surface)]';
    $resolvedBorderColor = $colorBorde ?? 'border-[var(--rm-border-soft)]';
@endphp

<div {{ $attributes->merge(['class' => "rm-card-metric rounded-[var(--rm-radius-card,16px)] border {$resolvedBorderColor} {$resolvedBgColor} {$variantBorderAccent} p-[18px] shadow-[var(--rm-shadow-sm)]"]) }}>
    <div class="flex items-center justify-between gap-2 mb-2">
        <p class="rm-metric-label">{{ $etiqueta }}</p>
        @if($icono)
            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $variantIconStyle }} border text-sm">
                <i class="ph-bold {{ $icono }}"></i>
            </span>
        @endif
    </div>

    <p class="rm-metric-value text-[30px] font-extrabold {{ $resolvedValueColor }} leading-tight">
        {{ $valor }}
    </p>

    @if($slot->isNotEmpty())
        <div class="rm-metric-meta mt-2 text-xs font-semibold text-[var(--rm-text-secondary)]">
            {{ $slot }}
        </div>
    @endif
</div>
