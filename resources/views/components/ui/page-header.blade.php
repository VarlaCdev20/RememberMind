{{--
 Componente: ui/page-header
 Contrato V2: overline opcional, title (Nunito Sans 32/800), subtitle (body/secondary),
 icon opcional (no forzado), actions slot.
--}}
@props([
    'titulo' => null,
    'subtitulo' => null,
    'title' => null,
    'subtitle' => null,
    'overline' => null,
    'icono' => null,
    'icon' => null,
    'color' => 'bg-[var(--rm-action-primary)]',
    'actions' => null,
])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedSubtitle = $subtitle ?? $subtitulo;
    $resolvedIcon = $icon ?? $icono;
    $resolvedActions = $actions ?? $slot;
@endphp

<header class="rm-page-header mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex items-center gap-3.5 min-w-0">
        @if($resolvedIcon)
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[var(--rm-radius-card,16px)] {{ $color }} text-[var(--rm-text-on-primary)] shadow-[var(--rm-shadow-sm)] transition-transform duration-200 hover:scale-105">
                <i class="ph-bold {{ $resolvedIcon }} text-2xl"></i>
            </span>
        @endif

        <div class="rm-page-title-group min-w-0">
            @if($overline)
                <p class="text-xs font-bold text-[var(--rm-action-primary)] uppercase tracking-wider mb-0.5">
                    {{ $overline }}
                </p>
            @endif

            @if($resolvedTitle)
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[var(--rm-text-primary)] leading-tight tracking-tight truncate">
                    {{ $resolvedTitle }}
                </h1>
            @endif

            @if($resolvedSubtitle)
                <p class="text-xs sm:text-sm font-medium text-[var(--rm-text-secondary)] mt-1 leading-snug">
                    {{ $resolvedSubtitle }}
                </p>
            @endif
        </div>
    </div>

    @if($resolvedActions && trim($resolvedActions) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-2.5">
            {{ $resolvedActions }}
        </div>
    @endif
</header>
