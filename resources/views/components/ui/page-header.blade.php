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

<header {{ $attributes->class(['rm-page-header']) }}>
    <div class="flex items-center gap-3.5 min-w-0">
        @if($resolvedIcon)
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--rm-radius-card,16px)] {{ $color }} text-[var(--rm-text-on-primary)] shadow-[var(--rm-shadow-sm)] transition-transform duration-200 hover:scale-105">
                <i class="ph-bold {{ $resolvedIcon }} text-xl"></i>
            </span>
        @endif

        <div class="rm-page-title-group min-w-0">
            @if($overline)
                <p class="text-xs font-bold text-[var(--rm-action-primary)] uppercase tracking-wider mb-0.5">
                    {{ $overline }}
                </p>
            @endif

            @if($resolvedTitle)
                <h1 class="text-2xl sm:text-[30px] font-extrabold text-[var(--rm-text-primary)] leading-tight tracking-tight break-words">
                    {{ $resolvedTitle }}
                </h1>
            @endif

            @if($resolvedSubtitle)
                <p class="text-xs font-medium text-[var(--rm-text-secondary)] mt-1 leading-snug">
                    {{ $resolvedSubtitle }}
                </p>
            @endif
        </div>
    </div>

    @if($resolvedActions && trim($resolvedActions) !== '')
        <div class="flex w-full flex-wrap items-center gap-2.5 lg:w-auto lg:justify-end">
            {{ $resolvedActions }}
        </div>
    @endif
</header>
