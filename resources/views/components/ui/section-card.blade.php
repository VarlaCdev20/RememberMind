{{--
 Componente: ui/section-card
 Contrato V2: title, subtitle opcional, actions opcional, body (slot), footer opcional.
 Agnóstico de dominio, utiliza tokens de superficie, borde y tipografía V2.
--}}
@props([
    'titulo' => null,
    'subtitulo' => null,
    'title' => null,
    'subtitle' => null,
    'icono' => null,
    'icon' => null,
    'acciones' => null,
    'actions' => null,
    'footer' => null,
])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedSubtitle = $subtitle ?? $subtitulo;
    $resolvedIcon = $icon ?? $icono;
    $resolvedActions = $actions ?? $acciones;
@endphp

<section {{ $attributes->merge(['class' => 'rm-card bg-[var(--rm-surface)] border border-[var(--rm-border)] rounded-[var(--rm-radius-card,16px)] shadow-[var(--rm-shadow-sm)] p-[18px] sm:p-5 transition-all']) }}>
    @if($resolvedTitle || $resolvedIcon || $resolvedActions)
        <header class="flex items-center justify-between gap-3 pb-3 mb-4 border-b border-[var(--rm-border-soft)]">
            <div class="flex items-center gap-3 min-w-0">
                @if($resolvedIcon)
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)]/10 text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/20 text-base shadow-2xs">
                        <i class="ph-bold {{ $resolvedIcon }}"></i>
                    </div>
                @endif
                <div class="min-w-0">
                    @if($resolvedTitle)
                        <h3 class="text-[15px] font-bold text-[var(--rm-text-primary)] leading-tight truncate">
                            {{ $resolvedTitle }}
                        </h3>
                    @endif
                    @if($resolvedSubtitle)
                        <p class="text-xs sm:text-sm font-medium text-[var(--rm-text-secondary)] mt-0.5 leading-snug truncate">
                            {{ $resolvedSubtitle }}
                        </p>
                    @endif
                </div>
            </div>
            @if($resolvedActions)
                <div class="flex items-center gap-2 shrink-0">
                    {{ $resolvedActions }}
                </div>
            @endif
        </header>
    @endif

    <div class="rm-card-content flex-1">
        {{ $slot }}
    </div>

    @if(isset($footer) && $footer)
        <footer class="mt-4 pt-3 border-t border-[var(--rm-border-soft)] flex items-center justify-between gap-3 text-xs text-[var(--rm-text-secondary)]">
            {{ $footer }}
        </footer>
    @endif
</section>
